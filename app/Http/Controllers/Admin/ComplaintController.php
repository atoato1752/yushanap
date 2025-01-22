<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Http\Requests\Admin\ComplaintUpdateRequest;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $complaints = Complaint::with(['query.user'])
            ->when($request->phone, function($query) use ($request) {
                $query->where('phone', 'like', "%{$request->phone}%");
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->date_range, function($query) use ($request) {
                $dates = explode(' - ', $request->date_range);
                $query->whereBetween('created_at', $dates);
            })
            ->latest()
            ->paginate(15);

        return view('admin.complaints.index', compact('complaints'));
    }

    public function show(Complaint $complaint)
    {
        $complaint->load('query.user');
        return view('admin.complaints.show', compact('complaint'));
    }

    public function update(ComplaintUpdateRequest $request, Complaint $complaint)
    {
        $complaint->update($request->validated());

        // 记录操作日志
        activity()
            ->performedOn($complaint)
            ->causedBy(auth()->user())
            ->log('更新投诉状态');

        return redirect()
            ->route('admin.complaints.index')
            ->with('success', '投诉状态已更新');
    }
} 