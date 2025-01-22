<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Query;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'total_users' => User::count(),
            'total_queries' => Query::count(),
            'pending_complaints' => Complaint::where('status', 'pending')->count(),
            'today_income' => Query::whereDate('created_at', today())
                ->where('payment_status', 'paid')
                ->sum('amount'),
            'recent_queries' => Query::with('user')
                ->latest()
                ->limit(10)
                ->get(),
            'query_stats' => Query::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(7)
                ->get()
        ];

        return view('admin.dashboard', $data);
    }
} 