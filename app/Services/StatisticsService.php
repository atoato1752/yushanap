<?php

namespace App\Services;

use App\Models\Query;
use App\Models\Agent;
use App\Models\Payment;
use App\Models\Complaint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class StatisticsService
{
    protected $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    /**
     * 获取总览数据
     */
    public function getOverview(): array
    {
        return Cache::remember('statistics:overview', 3600, function () {
            return [
                'today' => $this->getDailyStats(now()),
                'yesterday' => $this->getDailyStats(now()->subDay()),
                'month' => $this->getMonthlyStats(now()),
                'total' => $this->getTotalStats()
            ];
        });
    }

    /**
     * 获取趋势数据
     */
    public function getTrends(string $type = 'day', int $days = 7): array
    {
        $key = "statistics:trends:{$type}:{$days}";
        
        return Cache::remember($key, 3600, function () use ($type, $days) {
            $query = Query::select(
                DB::raw($type === 'day' ? 'DATE(created_at) as date' : 'DATE_FORMAT(created_at, "%Y-%m") as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(CASE WHEN payment_status = "paid" THEN 1 END) as paid'),
                DB::raw('SUM(CASE WHEN payment_status = "paid" THEN amount ELSE 0 END) as amount')
            )
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date');

            return $query->get()->toArray();
        });
    }

    /**
     * 获取代理商排行
     */
    public function getAgentRankings(string $type = 'earnings', int $limit = 10): array
    {
        $key = "statistics:agent_rankings:{$type}:{$limit}";

        return Cache::remember($key, 3600, function () use ($type, $limit) {
            if ($type === 'earnings') {
                return Agent::withSum('earnings', 'amount')
                    ->orderByDesc('earnings_sum_amount')
                    ->limit($limit)
                    ->get()
                    ->toArray();
            }

            return Agent::withCount('queries')
                ->orderByDesc('queries_count')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    /**
     * 生成每日报表
     */
    public function generateDailyReport(): void
    {
        $date = now()->subDay();
        $stats = $this->getDailyStats($date);

        try {
            DB::table('daily_reports')->insert([
                'date' => $date->format('Y-m-d'),
                'data' => json_encode($stats),
                'created_at' => now()
            ]);

            $this->logService->operation('statistics', 'generate_daily_report', [
                'date' => $date->format('Y-m-d')
            ]);
        } catch (\Exception $e) {
            $this->logService->error($e, [
                'action' => 'generate_daily_report',
                'date' => $date->format('Y-m-d')
            ]);
        }
    }

    /**
     * 获取每日统计
     */
    protected function getDailyStats(Carbon $date): array
    {
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        return [
            'queries' => [
                'total' => Query::whereBetween('created_at', [$start, $end])->count(),
                'paid' => Query::whereBetween('created_at', [$start, $end])
                    ->where('payment_status', 'paid')
                    ->count(),
                'amount' => Payment::whereBetween('created_at', [$start, $end])
                    ->where('status', 'success')
                    ->sum('amount')
            ],
            'agents' => [
                'total' => Agent::whereBetween('created_at', [$start, $end])->count(),
                'earnings' => DB::table('agent_earnings')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('amount')
            ],
            'complaints' => [
                'total' => Complaint::whereBetween('created_at', [$start, $end])->count(),
                'pending' => Complaint::whereBetween('created_at', [$start, $end])
                    ->where('status', 'pending')
                    ->count()
            ]
        ];
    }

    /**
     * 获取月度统计
     */
    protected function getMonthlyStats(Carbon $date): array
    {
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        return [
            'queries' => [
                'total' => Query::whereBetween('created_at', [$start, $end])->count(),
                'paid' => Query::whereBetween('created_at', [$start, $end])
                    ->where('payment_status', 'paid')
                    ->count(),
                'amount' => Payment::whereBetween('created_at', [$start, $end])
                    ->where('status', 'success')
                    ->sum('amount')
            ],
            'agents' => [
                'total' => Agent::whereBetween('created_at', [$start, $end])->count(),
                'earnings' => DB::table('agent_earnings')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('amount')
            ],
            'complaints' => [
                'total' => Complaint::whereBetween('created_at', [$start, $end])->count(),
                'resolved' => Complaint::whereBetween('created_at', [$start, $end])
                    ->where('status', 'resolved')
                    ->count()
            ]
        ];
    }

    /**
     * 获取总计统计
     */
    protected function getTotalStats(): array
    {
        return [
            'queries' => [
                'total' => Query::count(),
                'paid' => Query::where('payment_status', 'paid')->count(),
                'amount' => Payment::where('status', 'success')->sum('amount')
            ],
            'agents' => [
                'total' => Agent::count(),
                'earnings' => DB::table('agent_earnings')->sum('amount')
            ],
            'complaints' => [
                'total' => Complaint::count(),
                'resolved' => Complaint::where('status', 'resolved')->count()
            ]
        ];
    }
} 