<?php

namespace App\Services;

use App\Models\User;
use App\Models\Agent;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * 发送系统通知
     */
    public function sendSystem(string $title, string $content, array $data = []): void
    {
        Notification::create([
            'type' => 'system',
            'title' => $title,
            'content' => $content,
            'data' => $data,
            'is_broadcast' => true
        ]);
    }

    /**
     * 发送用户通知
     */
    public function sendToUser(User $user, string $title, string $content, array $data = []): void
    {
        $notification = new Notification([
            'type' => 'user',
            'title' => $title,
            'content' => $content,
            'data' => $data
        ]);

        $notification->notifiable()->associate($user);
        $notification->save();
    }

    /**
     * 发送代理商通知
     */
    public function sendToAgent(Agent $agent, string $title, string $content, array $data = []): void
    {
        $notification = new Notification([
            'type' => 'agent',
            'title' => $title,
            'content' => $content,
            'data' => $data
        ]);

        $notification->notifiable()->associate($agent);
        $notification->save();
    }

    /**
     * 发送查询完成通知
     */
    public function sendQueryCompleted(User $user, array $data): void
    {
        $this->sendToUser($user, 
            '查询完成通知', 
            '您的信用查询已完成，请及时查看结果。',
            array_merge(['type' => 'query_completed'], $data)
        );
    }

    /**
     * 发送投诉处理通知
     */
    public function sendComplaintHandled(User $user, array $data): void
    {
        $this->sendToUser($user,
            '投诉处理通知',
            '您的投诉已处理，请查看处理结果。',
            array_merge(['type' => 'complaint_handled'], $data)
        );
    }

    /**
     * 发送收益结算通知
     */
    public function sendEarningSettled(Agent $agent, array $data): void
    {
        $this->sendToAgent($agent,
            '收益结算通知',
            '您的收益已结算，请查看结算明细。',
            array_merge(['type' => 'earning_settled'], $data)
        );
    }

    /**
     * 获取用户通知列表
     */
    public function getUserNotifications(User $user, array $filters = [])
    {
        $query = Notification::where(function ($q) use ($user) {
            $q->where('is_broadcast', true)
                ->orWhere(function ($q) use ($user) {
                    $q->where('notifiable_type', User::class)
                        ->where('notifiable_id', $user->id);
                });
        });

        // 类型筛选
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // 已读状态筛选
        if (isset($filters['is_read'])) {
            $query->where('is_read', $filters['is_read']);
        }

        return $query->latest()->paginate(15);
    }

    /**
     * 获取代理商通知列表
     */
    public function getAgentNotifications(Agent $agent, array $filters = [])
    {
        $query = Notification::where(function ($q) use ($agent) {
            $q->where('is_broadcast', true)
                ->orWhere(function ($q) use ($agent) {
                    $q->where('notifiable_type', Agent::class)
                        ->where('notifiable_id', $agent->id);
                });
        });

        // 类型筛选
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // 已读状态筛选
        if (isset($filters['is_read'])) {
            $query->where('is_read', $filters['is_read']);
        }

        return $query->latest()->paginate(15);
    }

    /**
     * 标记通知为已读
     */
    public function markAsRead(array $ids): void
    {
        Notification::whereIn('id', $ids)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    /**
     * 获取未读通知数量
     */
    public function getUnreadCount($notifiable): int
    {
        return Notification::where(function ($q) use ($notifiable) {
            $q->where('is_broadcast', true)
                ->orWhere(function ($q) use ($notifiable) {
                    $q->where('notifiable_type', get_class($notifiable))
                        ->where('notifiable_id', $notifiable->id);
                });
        })
        ->where('is_read', false)
        ->count();
    }

    /**
     * 清理过期通知
     */
    public function cleanExpiredNotifications(int $days = 30): void
    {
        $date = now()->subDays($days);
        
        Notification::where('created_at', '<', $date)
            ->where('is_read', true)
            ->delete();
    }
} 