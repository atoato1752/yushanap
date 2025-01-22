<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Query;
use App\Models\Agent;
use App\Models\Complaint;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 今日数据统计
        $today = Carbon::today();
        $todayStats = [
            'queries_count' => Query::whereDate('created_at', $today)->count(),
            'paid_amount' => Query::whereDate('created_at', $today)
                ->where('payment_status', 'paid')
                ->sum('amount'),
            'complaints_count' => Complaint::whereDate('created_at', $today)->count()
        ];

        // 最近7天查询趋势
        $queryTrend = Query::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(CASE WHEN payment_status = "paid" THEN amount ELSE 0 END) as amount')
        )
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->get();

        // 代理商排行
        $topAgents = Agent::withCount('queries')
            ->withSum('earnings', 'amount')
            ->orderByDesc('queries_count')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('todayStats', 'queryTrend', 'topAgents'));
    }
} 