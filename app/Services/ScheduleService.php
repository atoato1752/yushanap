<?php

namespace App\Services;

use Illuminate\Console\Scheduling\Schedule;

class ScheduleService
{
    protected $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    /**
     * 注册计划任务
     */
    public function register(Schedule $schedule): void
    {
        // 每天凌晨清理过期日志
        $schedule->call(function () {
            app(LogService::class)->cleanExpiredLogs(30);
        })->daily();

        // 每天凌晨清理过期通知
        $schedule->call(function () {
            app(NotificationService::class)->cleanExpiredNotifications(30);
        })->daily();

        // 每小时结算代理商收益
        $schedule->call(function () {
            $this->settleAgentEarnings();
        })->hourly();

        // 每5分钟检查超时订单
        $schedule->call(function () {
            $this->checkTimeoutOrders();
        })->everyFiveMinutes();

        // 每天生成统计报表
        $schedule->call(function () {
            $this->generateDailyReport();
        })->dailyAt('01:00');
    }

    /**
     * 结算代理商收益
     */
    protected function settleAgentEarnings(): void
    {
        try {
            app(AgentService::class)->settleEarnings();
        } catch (\Exception $e) {
            $this->logService->error($e, ['task' => 'settle_earnings']);
        }
    }

    /**
     * 检查超时订单
     */
    protected function checkTimeoutOrders(): void
    {
        try {
            app(QueryService::class)->handleTimeoutQueries();
        } catch (\Exception $e) {
            $this->logService->error($e, ['task' => 'check_timeout_orders']);
        }
    }

    /**
     * 生成每日报表
     */
    protected function generateDailyReport(): void
    {
        try {
            app(StatisticsService::class)->generateDailyReport();
        } catch (\Exception $e) {
            $this->logService->error($e, ['task' => 'generate_daily_report']);
        }
    }
} 