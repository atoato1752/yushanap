<?php

namespace App\Http\Controllers;

use App\Models\Query;
use App\Models\Complaint;
use App\Http\Requests\ComplaintRequest;

class ComplaintController extends Controller
{
    public function create(Request $request)
    {
        $query = Query::findOrFail($request->query_id);
        return view('query.complaint', compact('query'));
    }

    public function store(ComplaintRequest $request)
    {
        $complaint = Complaint::create($request->validated());

        return redirect()
            ->route('queries.show', $complaint->query_id)
            ->with('success', '投诉已提交，我们会尽快处理');
    }

    public function status(Complaint $complaint)
    {
        return response()->json([
            'status' => $complaint->status,
            'admin_remark' => $complaint->admin_remark
        ]);
    }
} 