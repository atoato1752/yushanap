<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Http\Requests\Admin\AgentRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $agents = Agent::with('parent')
            ->when($request->username, function($query) use ($request) {
                $query->where('username', 'like', "%{$request->username}%");
            })
            ->when($request->status !== null, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(15);

        return view('admin.agents.index', compact('agents'));
    }

    public function create()
    {
        $agents = Agent::where('status', true)->get();
        return view('admin.agents.create', compact('agents'));
    }

    public function store(AgentRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password'] ?? '123456');

        Agent::create($data);

        return redirect()
            ->route('admin.agents.index')
            ->with('success', '代理商创建成功');
    }

    public function edit(Agent $agent)
    {
        $agents = Agent::where('id', '!=', $agent->id)
            ->where('status', true)
            ->get();
            
        return view('admin.agents.edit', compact('agent', 'agents'));
    }

    public function update(AgentRequest $request, Agent $agent)
    {
        $data = $request->validated();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $agent->update($data);

        return redirect()
            ->route('admin.agents.index')
            ->with('success', '代理商更新成功');
    }

    public function earnings(Agent $agent)
    {
        $earnings = $agent->earnings()
            ->with('query')
            ->latest()
            ->paginate(15);

        return view('admin.agents.earnings', compact('agent', 'earnings'));
    }
} 