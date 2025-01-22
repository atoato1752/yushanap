<?php

namespace App\Services;

use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessQuery;
use App\Jobs\GenerateReport;
use App\Jobs\SendNotification;
use App\Jobs\SettleEarnings;
use Exception;

class QueueService
{
    protected $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    /**
     * 添加查询处理任务
     */
    public function addQueryJob($query): void
    {
        try {
            Queue::push(new ProcessQuery($query));

            $this->logService->operation('queue', 'add_query_job', [
                'query_id' => $query->id
            ]);
        } catch (Exception $e) {
            $this->logService->error($e, [
                'job' => 'process_query',
                'query_id' => $query->id
            ]);
            throw $e;
        }
    }

    /**
     * 添加报告生成任务
     */
    public function addReportJob($query): void
    {
        try {
            Queue::push(new GenerateReport($query));

            $this->logService->operation('queue', 'add_report_job', [
                'query_id' => $query->id
            ]);
        } catch (Exception $e) {
            $this->logService->error($e, [
                'job' => 'generate_report',
                'query_id' => $query->id
            ]);
            throw $e;
        }
    }

    /**
     * 添加通知发送任务
     */
    public function addNotificationJob($notification): void
    {
        try {
            Queue::push(new SendNotification($notification));

            $this->logService->operation('queue', 'add_notification_job', [
                'notification_id' => $notification->id
            ]);
        } catch (Exception $e) {
            $this->logService->error($e, [
                'job' => 'send_notification',
                'notification_id' => $notification->id
            ]);
            throw $e;
        }
    }

    /**
     * 添加收益结算任务
     */
    public function addSettlementJob($agent, $earnings): void
    {
        try {
            Queue::push(new SettleEarnings($agent, $earnings));

            $this->logService->operation('queue', 'add_settlement_job', [
                'agent_id' => $agent->id,
                'earnings_count' => count($earnings)
            ]);
        } catch (Exception $e) {
            $this->logService->error($e, [
                'job' => 'settle_earnings',
                'agent_id' => $agent->id
            ]);
            throw $e;
        }
    }

    /**
     * 重试失败的任务
     */
    public function retryFailedJob($jobId): void
    {
        try {
            Queue::retry($jobId);

            $this->logService->operation('queue', 'retry_job', [
                'job_id' => $jobId
            ]);
        } catch (Exception $e) {
            $this->logService->error($e, [
                'job' => 'retry_failed',
                'job_id' => $jobId
            ]);
            throw $e;
        }
    }

    /**
     * 清理失败的任务
     */
    public function clearFailedJobs(): void
    {
        try {
            Queue::flush();
            
            $this->logService->operation('queue', 'clear_failed_jobs', []);
        } catch (Exception $e) {
            $this->logService->error($e, ['action' => 'clear_failed_jobs']);
            throw $e;
        }
    }
} 