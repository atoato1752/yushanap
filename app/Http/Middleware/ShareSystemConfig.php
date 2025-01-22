<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SystemConfig;
use Illuminate\Support\Facades\View;

class ShareSystemConfig
{
    public function handle(Request $request, Closure $next)
    {
        // 从数据库获取所有系统配置
        $configs = SystemConfig::all()->pluck('value', 'key_name')->toArray();
        
        // 共享到所有视图
        View::share('configs', $configs);

        return $next($request);
    }
} 