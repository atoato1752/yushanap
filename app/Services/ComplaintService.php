<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Query;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class ComplaintService
{
    /**
     * 创建投诉
     */
    public function create(Query $query, array $data): Complaint
    {
        return DB::transaction(function () use ($query, $data) {
            // 检查是否已存在投诉
            if ($query->complaint()->exists()) {
                throw new Exception('该查询已存在投诉记录');
            }

            // 创建投诉记录
            $complaint = new Complaint([
                'content' => $data['content'],
                'status' => 'pending',
                'images' => $data['images'] ?? null
            ]);

            $complaint->query()->associate($query);
            $complaint->user()->associate($query->user);
            $complaint->save();

            // 记录操作日志
            $complaint->logs()->create([
                'status' => 'pending',
                'remark' => '用户提交投诉'
            ]);

            return $complaint;
        });
    }

    /**
     * 处理投诉
     */
    public function handle(Complaint $complaint, array $data): Complaint
    {
        return DB::transaction(function () use ($complaint, $data) {
            // 更新投诉状态
            $complaint->update([
                'status' => $data['status'],
                'admin_remark' => $data['admin_remark']
            ]);

            // 记录操作日志
            $complaint->logs()->create([
                'status' => $data['status'],
                'remark' => $data['admin_remark'],
                'admin_id' => auth()->id()
            ]);

            // 如果已解决，可以进行退款等操作
            if ($data['status'] === 'resolved' && !empty($data['refund'])) {
                $this->handleRefund($complaint);
            }

            return $complaint;
        });
    }

    /**
     * 搜索投诉
     */
    public function search(array $filters)
    {
        $query = Complaint::with(['user', 'query']);

        // 手机号搜索
        if (!empty($filters['phone'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('phone', 'like', "%{$filters['phone']}%");
            });
        }

        // 状态筛选
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
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

    /**
     * 获取投诉统计
     */
    public function getStats(): array
    {
        $today = now()->startOfDay();
        $todayStats = Complaint::where('created_at', '>=', $today)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending')
            ->selectRaw('COUNT(CASE WHEN status = "processing" THEN 1 END) as processing')
            ->selectRaw('COUNT(CASE WHEN status = "resolved" THEN 1 END) as resolved')
            ->first();

        $last7Days = Complaint::where('created_at', '>=', now()->subDays(7)->startOfDay())
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('date')
            ->get();

        return [
            'today' => [
                'total' => $todayStats->total,
                'pending' => $todayStats->pending,
                'processing' => $todayStats->processing,
                'resolved' => $todayStats->resolved
            ],
            'trend' => $last7Days->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count
                ];
            })
        ];
    }

    /**
     * 处理退款
     */
    protected function handleRefund(Complaint $complaint): void
    {
        $query = $complaint->query;

        // 检查是否已退款
        if ($query->payment_status === 'refunded') {
            return;
        }

        // 更新查询状态
        $query->update([
            'payment_status' => 'refunded',
            'status' => 'cancelled'
        ]);

        // 如果是代理商查询，需要处理代理商余额
        if ($query->agent) {
            $earning = $query->agent->earnings()
                ->where('query_id', $query->id)
                ->first();

            if ($earning) {
                // 扣除代理商余额
                $query->agent->decrement('balance', $earning->amount);

                // 更新收益记录
                $earning->update([
                    'status' => 'refunded',
                    'refunded_at' => now()
                ]);
            }
        }

        // TODO: 调用支付接口进行实际退款操作
    }
} 