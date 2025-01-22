<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use App\Services\YushanService;

class UpdateQueryStatus
{
    protected $yushanService;

    public function __construct(YushanService $yushanService)
    {
        $this->yushanService = $yushanService;
    }

    public function handle(PaymentSucceeded $event)
    {
        $payment = $event->payment;
        $query = $payment->query;

        // 更新查询状态
        $query->update([
            'payment_status' => 'paid'
        ]);

        // 获取信用报告
        $report = $this->yushanService->getReport($query);

        // 更新报告内容
        $query->update([
            'report_content' => $report
        ]);
    }
} 