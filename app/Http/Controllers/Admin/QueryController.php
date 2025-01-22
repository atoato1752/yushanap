<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Query;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class QueryController extends Controller
{
    public function index(Request $request)
    {
        $queries = Query::with(['user', 'agent'])
            ->when($request->phone, function($query) use ($request) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('phone', 'like', "%{$request->phone}%");
                });
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('payment_status', $request->status);
            })
            ->when($request->date_range, function($query) use ($request) {
                $dates = explode(' - ', $request->date_range);
                $query->whereBetween('created_at', $dates);
            })
            ->latest()
            ->paginate(15);

        return view('admin.queries.index', compact('queries'));
    }

    public function show(Query $query)
    {
        $query->load(['user', 'agent', 'payment', 'complaint']);
        return view('admin.queries.show', compact('query'));
    }

    public function export(Request $request)
    {
        return Excel::download(new QueriesExport($request->all()), 'queries.xlsx');
    }
} 