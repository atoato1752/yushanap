<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentEarning;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;

class AgentService
{
    /**
     * 创建代理商
     */
    public function create(array $data): Agent
    {
        return DB::transaction(function () use ($data) {
            // 检查用户名是否已存在
            if (Agent::where('username', $data['username'])->exists()) {
                throw new Exception('用户名已存在');
            }

            // 创建代理商
            $agent = new Agent([
                'username' => $data['username'],
                'name' => $data['name'],
                'password' => Hash::make($data['password'] ?: '123456'),
                'cost_price' => $data['cost_price'],
                'selling_price' => $data['selling_price'],
                'status' => $data['status'] ?? true,
                'balance' => 0
            ]);

            // 设置上级代理
            if (!empty($data['parent_id'])) {
                $parent = Agent::findOrFail($data['parent_id']);
                
                // 验证价格设置
                if ($data['cost_price'] < $parent->cost_price) {
                    throw new Exception('成本价不能低于上级代理商的成本价');
                }
                if ($data['selling_price'] < $data['cost_price']) {
                    throw new Exception('销售价不能低于成本价');
                }

                $agent->parent()->associate($parent);
            }

            $agent->save();
            return $agent;
        });
    }

    /**
     * 更新代理商
     */
    public function update(Agent $agent, array $data): Agent
    {
        return DB::transaction(function () use ($agent, $data) {
            // 更新基本信息
            $agent->name = $data['name'];
            if (!empty($data['password'])) {
                $agent->password = Hash::make($data['password']);
            }

            // 更新价格
            if ($agent->cost_price != $data['cost_price'] || 
                $agent->selling_price != $data['selling_price']) {
                
                // 验证价格设置
                if ($agent->parent && $data['cost_price'] < $agent->parent->cost_price) {
                    throw new Exception('成本价不能低于上级代理商的成本价');
                }
                if ($data['selling_price'] < $data['cost_price']) {
                    throw new Exception('销售价不能低于成本价');
                }
                if ($agent->children()->exists() && 
                    $agent->children()->min('cost_price') < $data['selling_price']) {
                    throw new Exception('销售价不能高于下级代理商的成本价');
                }

                $agent->cost_price = $data['cost_price'];
                $agent->selling_price = $data['selling_price'];
            }

            // 更新上级代理
            if (isset($data['parent_id']) && $agent->parent_id != $data['parent_id']) {
                if ($data['parent_id']) {
                    $parent = Agent::findOrFail($data['parent_id']);
                    if ($data['cost_price'] < $parent->cost_price) {
                        throw new Exception('成本价不能低于上级代理商的成本价');
                    }
                    $agent->parent()->associate($parent);
                } else {
                    $agent->parent()->dissociate();
                }
            }

            $agent->status = $data['status'] ?? $agent->status;
            $agent->save();

            return $agent;
        });
    }

    /**
     * 搜索代理商
     */
    public function search(array $filters)
    {
        $query = Agent::with('parent');

        // 用户名搜索
        if (!empty($filters['username'])) {
            $query->where('username', 'like', "%{$filters['username']}%");
        }

        // 状态筛选
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate(15);
    }

    /**
     * 获取代理商收益
     */
    public function getEarnings(Agent $agent, array $filters)
    {
        $query = $agent->earnings()->with('query');

        // 结算状态筛选
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // 日期范围筛选
        if (!empty($filters['date_range'])) {
            [$start, $end] = explode(' - ', $filters['date_range']);
            $query->whereBetween('created_at', [
                $start . ' 00:00:00',
                $end . ' 23:59:59'
            ]);
        }

        return $query->latest()->paginate(15);
    }

    /**
     * 结算收益
     */
    public function settleEarnings(Agent $agent, array $earningIds): void
    {
        DB::transaction(function () use ($agent, $earningIds) {
            $earnings = AgentEarning::whereIn('id', $earningIds)
                ->where('agent_id', $agent->id)
                ->where('status', 'pending')
                ->get();

            foreach ($earnings as $earning) {
                $earning->update([
                    'status' => 'settled',
                    'settled_at' => now()
                ]);
            }
        });
    }

    /**
     * 获取代理商层级树
     */
    public function getAgentTree(?Agent $parent = null): array
    {
        $query = Agent::query();

        if ($parent) {
            $query->where('parent_id', $parent->id);
        } else {
            $query->whereNull('parent_id');
        }

        $agents = $query->get();
        $tree = [];

        foreach ($agents as $agent) {
            $node = [
                'id' => $agent->id,
                'name' => $agent->name,
                'username' => $agent->username,
                'cost_price' => $agent->cost_price,
                'selling_price' => $agent->selling_price,
                'balance' => $agent->balance,
                'status' => $agent->status,
                'children' => $this->getAgentTree($agent)
            ];
            $tree[] = $node;
        }

        return $tree;
    }
} 