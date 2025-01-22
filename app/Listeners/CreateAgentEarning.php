<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;

class CreateAgentEarning
{
    public function handle(PaymentSucceeded $event)
    {
        $payment = $event->payment;
        $query = $payment->query;

        // 如果查询有代理商，创建代理商收益记录
        if ($query->agent_id) {
            $agent = $query->agent;
            $earning = $query->amount - $agent->cost_price;

            $query->agentEarning()->create([
                'agent_id' => $agent->id,
                'amount' => $earning
            ]);

            // 更新代理商余额
            $agent->increment('balance', $earning);
        }
    }
} 