<?php

namespace App\Services;

use App\Models\Query;
use App\Models\User;
use App\Models\Agent;
use Illuminate\Support\Facades\DB;
use Exception;

class QueryService
{
    protected $yushanService;
    protected $paymentService;

    public function __construct(YushanService $yushanService, PaymentService $paymentService)
    {
        $this->yushanService = $yushanService;
        $this->paymentService = $paymentService;
    }

    /**
     * 创建查询
     */
    public function create(array $data, User $user, ?Agent $agent = null): Query
    {
        return DB::transaction(function () use ($data, $user, $agent) {
            // 创建查询记录
            $query = new Query([
                'name' => $data['name'],
                'id_card' => $data['id_card'],
                'payment_type' => $data['payment_type'],
                'payment_status' => 'pending',
                'status' => 'pending'
            ]);

            if ($agent) {
                $query->agent()->associate($agent);
                $query->amount = $agent->selling_price;
            } else {
                $query->amount = config('payment.auth_code.price');
            }

            $user->queries()->save($query);

            // 如果是授权码支付，验证授权码
            if ($data['payment_type'] === 'auth_code') {
                if (!$this->paymentService->verifyAuthCode($data['auth_code'])) {
                    throw new Exception('无效的授权码');
                }
                $query->update([
                    'payment_status' => 'paid',
                    'status' => 'processing'
                ]);
            }

            return $query;
        });
    }

    /**
     * 获取查询报告
     */
    public function getReport(Query $query): array
    {
        // 检查支付状态
        if ($query->payment_status !== 'paid') {
            throw new Exception('请先完成支付');
        }

        // 如果已有报告数据，直接返回
        if ($query->report_content) {
            return $query->report_content;
        }

        try {
            // 调用羽山API获取报告
            $report = $this->yushanService->getCreditReport($query->name, $query->id_card);

            // 更新查询状态和报告内容
            $query->update([
                'status' => 'completed',
                'report_content' => $report,
                'completed_at' => now()
            ]);

            return $report;
        } catch (Exception $e) {
            // 更新查询状态为失败
            $query->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * 处理支付成功
     */
    public function handlePaymentSuccess(Query $query): void
    {
        $query->update([
            'payment_status' => 'paid',
            'status' => 'processing'
        ]);

        // 异步获取报告
        dispatch(function () use ($query) {
            try {
                $this->getReport($query);
            } catch (Exception $e) {
                \Log::error('自动获取报告失败', [
                    'query_id' => $query->id,
                    'error' => $e->getMessage()
                ]);
            }
        })->afterCommit();
    }

    /**
     * 获取查询统计
     */
    public function getStats(?Agent $agent = null): array
    {
        $query = Query::query();

        if ($agent) {
            $query->where('agent_id', $agent->id);
        }

        $today = now()->startOfDay();
        $todayStats = $query->clone()
            ->where('created_at', '>=', $today)
            ->selectRaw('COUNT(*) as queries_count')
            ->selectRaw('SUM(CASE WHEN payment_status = "paid" THEN amount ELSE 0 END) as paid_amount')
            ->selectRaw('COUNT(CASE WHEN status = "failed" THEN 1 END) as failed_count')
            ->first();

        $last7Days = $query->clone()
            ->where('created_at', '>=', now()->subDays(7)->startOfDay())
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(CASE WHEN payment_status = "paid" THEN amount ELSE 0 END) as amount')
            ->groupBy('date')
            ->get();

        return [
            'today' => [
                'queries_count' => $todayStats->queries_count,
                'paid_amount' => $todayStats->paid_amount ?? 0,
                'failed_count' => $todayStats->failed_count
            ],
            'trend' => $last7Days->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count,
                    'amount' => $item->amount
                ];
            })
        ];
    }

    /**
     * 搜索查询记录
     */
    public function search(array $filters, ?Agent $agent = null)
    {
        $query = Query::with(['user', 'agent', 'payment']);

        if ($agent) {
            $query->where('agent_id', $agent->id);
        }

        // 手机号搜索
        if (!empty($filters['phone'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('phone', 'like', "%{$filters['phone']}%");
            });
        }

        // 支付状态筛选
        if (!empty($filters['status'])) {
            $query->where('payment_status', $filters['status']);
        }

        // 日期范围筛选
        if (!empty($filters['date_range'])) {
            [$start, $end] = explode(' - ', $filters['date_range']);
            $query->whereBetween('created_at', [
                $start . ' 00:00:00',
                $end . ' 23:59:59'
            ]);
        }

        return $query->latest()->paginate(15);
    }
} 