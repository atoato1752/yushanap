<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Exception;

class UserService
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * 发送验证码
     */
    public function sendVerificationCode(string $phone): void
    {
        // 发送验证码
        $code = $this->smsService->sendVerificationCode($phone);

        // 记录日志
        \Log::info('发送验证码', [
            'phone' => $phone,
            'code' => $code,
            'ip' => request()->ip()
        ]);
    }

    /**
     * 验证码登录
     */
    public function loginWithCode(string $phone, string $code): User
    {
        // 验证验证码
        if (!$this->smsService->verifyCode($phone, $code)) {
            throw new Exception('验证码错误或已过期');
        }

        // 查找或创建用户
        $user = User::firstOrCreate(
            ['phone' => $phone],
            ['name' => substr_replace($phone, '****', 3, 4)]
        );

        // 更新登录信息
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip()
        ]);

        return $user;
    }

    /**
     * 更新用户信息
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'avatar' => $data['avatar'] ?? $user->avatar
        ]);

        return $user;
    }

    /**
     * 获取用户查询记录
     */
    public function getQueries(User $user, array $filters = [])
    {
        $query = $user->queries()->with(['payment', 'complaint']);

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

    /**
     * 获取用户投诉记录
     */
    public function getComplaints(User $user, array $filters = [])
    {
        $query = $user->complaints()->with('query');

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
     * 获取用户统计数据
     */
    public function getStats(User $user): array
    {
        $queryStats = $user->queries()
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('COUNT(CASE WHEN payment_status = "paid" THEN 1 END) as paid_count')
            ->selectRaw('SUM(CASE WHEN payment_status = "paid" THEN amount ELSE 0 END) as total_amount')
            ->first();

        $complaintStats = $user->complaints()
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count')
            ->selectRaw('COUNT(CASE WHEN status = "resolved" THEN 1 END) as resolved_count')
            ->first();

        return [
            'queries' => [
                'total' => $queryStats->total_count,
                'paid' => $queryStats->paid_count,
                'amount' => $queryStats->total_amount ?? 0
            ],
            'complaints' => [
                'total' => $complaintStats->total_count,
                'pending' => $complaintStats->pending_count,
                'resolved' => $complaintStats->resolved_count
            ]
        ];
    }

    /**
     * 检查用户是否可以查询
     */
    public function canQuery(User $user): bool
    {
        // 检查是否有未支付的查询
        $hasPendingQuery = $user->queries()
            ->where('payment_status', 'pending')
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();

        if ($hasPendingQuery) {
            throw new Exception('您有未支付的查询，请先完成支付');
        }

        // 检查每日查询次数限制
        $todayQueries = $user->queries()
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        if ($todayQueries >= config('services.yushan.daily_limit', 5)) {
            throw new Exception('今日查询次数已达上限');
        }

        return true;
    }
} 