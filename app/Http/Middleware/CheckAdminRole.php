<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user('admin');

        if (!$user || !in_array($user->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '没有权限访问'], 403);
            }
            return redirect()->route('admin.login')
                ->with('error', '没有权限访问该页面');
        }

        return $next($request);
    }
} 