<?php

namespace App\Services;

use App\Models\SystemLog;
use App\Models\OperationLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class LogService
{
    /**
     * 记录系统日志
     */
    public function system(string $type, string $message, array $data = []): void
    {
        SystemLog::create([
            'type' => $type,
            'message' => $message,
            'data' => $data,
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent()
        ]);
    }

    /**
     * 记录操作日志
     */
    public function operation(string $module, string $action, array $data = []): void
    {
        $user = Auth::user();

        OperationLog::create([
            'user_id' => $user ? $user->id : null,
            'user_type' => $user ? get_class($user) : null,
            'module' => $module,
            'action' => $action,
            'data' => $data,
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method()
        ]);
    }

    /**
     * 记录异常日志
     */
    public function error(\Throwable $e, array $context = []): void
    {
        $this->system('error', $e->getMessage(), array_merge([
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], $context));
    }

    /**
     * 记录查询日志
     */
    public function query(string $action, array $data): void
    {
        $this->operation('query', $action, $data);
    }

    /**
     * 记录支付日志
     */
    public function payment(string $action, array $data): void
    {
        $this->operation('payment', $action, $data);
    }

    /**
     * 记录代理商日志
     */
    public function agent(string $action, array $data): void
    {
        $this->operation('agent', $action, $data);
    }

    /**
     * 记录投诉日志
     */
    public function complaint(string $action, array $data): void
    {
        $this->operation('complaint', $action, $data);
    }

    /**
     * 搜索系统日志
     */
    public function searchSystemLogs(array $filters = [])
    {
        $query = SystemLog::query();

        // 类型筛选
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // 关键词搜索
        if (!empty($filters['keyword'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('message', 'like', "%{$filters['keyword']}%")
                    ->orWhere('data', 'like', "%{$filters['keyword']}%");
            });
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
     * 搜索操作日志
     */
    public function searchOperationLogs(array $filters = [])
    {
        $query = OperationLog::with('user');

        // 模块筛选
        if (!empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        // 操作筛选
        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        // 用户筛选
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
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
     * 清理过期日志
     */
    public function cleanExpiredLogs(int $days = 30): void
    {
        $date = now()->subDays($days);

        SystemLog::where('created_at', '<', $date)->delete();
        OperationLog::where('created_at', '<', $date)->delete();
    }
} 