<?php

namespace App\Services;

use App\Models\Query;
use App\Models\User;
use App\Models\Agent;
use App\Events\QueryCompleted;
use App\Events\PaymentSucceeded;
use App\Events\ComplaintHandled;
use App\Events\AgentEarningSettled;
use Illuminate\Support\Facades\Event;

class EventService
{
    protected $notificationService;
    protected $logService;

    public function __construct(NotificationService $notificationService, LogService $logService)
    {
        $this->notificationService = $notificationService;
        $this->logService = $logService;
    }

    /**
     * 触发查询完成事件
     */
    public function queryCompleted(Query $query): void
    {
        try {
            event(new QueryCompleted($query));

            // 发送通知
            $this->notificationService->sendQueryCompleted($query->user, [
                'query_id' => $query->id,
                'name' => $query->name,
                'id_card' => $query->id_card
            ]);

            // 记录日志
            $this->logService->operation('event', 'query_completed', [
                'query_id' => $query->id,
                'user_id' => $query->user_id
            ]);
        } catch (\Exception $e) {
            $this->logService->error($e, [
                'event' => 'query_completed',
                'query_id' => $query->id
            ]);
        }
    }

    /**
     * 触发支付成功事件
     */
    public function paymentSucceeded(Query $query): void
    {
        try {
            event(new PaymentSucceeded($query));

            // 发送通知
            $this->notificationService->sendToUser($query->user, 
                '支付成功通知',
                '您的查询订单支付成功，我们将尽快为您处理。',
                [
                    'type' => 'payment_succeeded',
                    'query_id' => $query->id,
                    'amount' => $query->amount
                ]
            );

            // 记录日志
            $this->logService->operation('event', 'payment_succeeded', [
                'query_id' => $query->id,
                'payment_id' => $query->payment_id
            ]);
        } catch (\Exception $e) {
            $this->logService->error($e, [
                'event' => 'payment_succeeded',
                'query_id' => $query->id
            ]);
        }
    }

    /**
     * 触发投诉处理事件
     */
    public function complaintHandled(Query $query): void
    {
        try {
            event(new ComplaintHandled($query->complaint));

            // 发送通知
            $this->notificationService->sendComplaintHandled($query->user, [
                'query_id' => $query->id,
                'complaint_id' => $query->complaint->id,
                'status' => $query->complaint->status
            ]);

            // 记录日志
            $this->logService->operation('event', 'complaint_handled', [
                'query_id' => $query->id,
                'complaint_id' => $query->complaint->id
            ]);
        } catch (\Exception $e) {
            $this->logService->error($e, [
                'event' => 'complaint_handled',
                'query_id' => $query->id
            ]);
        }
    }

    /**
     * 触发代理商收益结算事件
     */
    public function agentEarningSettled(Agent $agent, array $earnings): void
    {
        try {
            event(new AgentEarningSettled($agent, $earnings));

            // 发送通知
            $this->notificationService->sendEarningSettled($agent, [
                'earnings' => $earnings,
                'total_amount' => collect($earnings)->sum('amount')
            ]);

            // 记录日志
            $this->logService->operation('event', 'earning_settled', [
                'agent_id' => $agent->id,
                'earnings' => $earnings
            ]);
        } catch (\Exception $e) {
            $this->logService->error($e, [
                'event' => 'earning_settled',
                'agent_id' => $agent->id
            ]);
        }
    }

    /**
     * 注册事件监听器
     */
    public function registerListeners(): void
    {
        Event::listen(QueryCompleted::class, function ($event) {
            $this->handleQueryCompleted($event->query);
        });

        Event::listen(PaymentSucceeded::class, function ($event) {
            $this->handlePaymentSucceeded($event->query);
        });

        Event::listen(ComplaintHandled::class, function ($event) {
            $this->handleComplaintHandled($event->complaint);
        });

        Event::listen(AgentEarningSettled::class, function ($event) {
            $this->handleAgentEarningSettled($event->agent, $event->earnings);
        });
    }

    /**
     * 处理查询完成事件
     */
    protected function handleQueryCompleted(Query $query): void
    {
        // 更新查询状态
        $query->update(['status' => 'completed']);

        // 生成报告
        app(ReportService::class)->generate($query);
    }

    /**
     * 处理支付成功事件
     */
    protected function handlePaymentSucceeded(Query $query): void
    {
        // 更新查询状态
        $query->update([
            'payment_status' => 'paid',
            'status' => 'pending'
        ]);

        // 处理代理商收益
        if ($query->agent) {
            app(PaymentService::class)->handleAgentEarning($query->payment);
        }
    }

    /**
     * 处理投诉处理事件
     */
    protected function handleComplaintHandled($complaint): void
    {
        // 更新投诉状态
        $complaint->update([
            'status' => 'resolved',
            'resolved_at' => now()
        ]);

        // 如果需要退款
        if ($complaint->need_refund) {
            app(PaymentService::class)->handleRefund(
                $complaint->query->payment,
                '投诉处理-退款'
            );
        }
    }

    /**
     * 处理代理商收益结算事件
     */
    protected function handleAgentEarningSettled(Agent $agent, array $earnings): void
    {
        foreach ($earnings as $earning) {
            $earning->update([
                'status' => 'settled',
                'settled_at' => now()
            ]);
        }
    }
} 