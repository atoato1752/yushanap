<?php

namespace App\Services;

use App\Models\User;
use App\Models\Agent;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Exception;

class AuthService
{
    protected $smsService;
    protected $logService;

    public function __construct(SmsService $smsService, LogService $logService)
    {
        $this->smsService = $smsService;
        $this->logService = $logService;
    }

    /**
     * 用户登录
     */
    public function userLogin(string $phone, string $code): array
    {
        // 验证验证码
        if (!$this->smsService->verifyCode($phone, $code)) {
            throw new Exception('验证码错误或已过期');
        }

        // 查找或创建用户
        $user = User::firstOrCreate(
            ['phone' => $phone],
            ['name' => substr_replace($phone, '****', 3, 4)]
        );

        // 更新登录信息
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip()
        ]);

        // 生成token
        $token = $user->createToken('user')->plainTextToken;

        // 记录日志
        $this->logService->operation('auth', 'user_login', [
            'user_id' => $user->id,
            'phone' => $phone,
            'ip' => request()->ip()
        ]);

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    /**
     * 代理商登录
     */
    public function agentLogin(string $username, string $password): array
    {
        $agent = Agent::where('username', $username)->first();

        if (!$agent || !Hash::check($password, $agent->password)) {
            throw new Exception('用户名或密码错误');
        }

        if (!$agent->status) {
            throw new Exception('账号已被禁用');
        }

        // 更新登录信息
        $agent->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip()
        ]);

        // 生成token
        $token = $agent->createToken('agent')->plainTextToken;

        // 记录日志
        $this->logService->operation('auth', 'agent_login', [
            'agent_id' => $agent->id,
            'username' => $username,
            'ip' => request()->ip()
        ]);

        return [
            'agent' => $agent,
            'token' => $token
        ];
    }

    /**
     * 管理员登录
     */
    public function adminLogin(string $username, string $password): array
    {
        $admin = Admin::where('username', $username)->first();

        if (!$admin || !Hash::check($password, $admin->password)) {
            throw new Exception('用户名或密码错误');
        }

        if (!$admin->status) {
            throw new Exception('账号已被禁用');
        }

        // 更新登录信息
        $admin->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip()
        ]);

        // 生成token
        $token = $admin->createToken('admin')->plainTextToken;

        // 记录日志
        $this->logService->operation('auth', 'admin_login', [
            'admin_id' => $admin->id,
            'username' => $username,
            'ip' => request()->ip()
        ]);

        return [
            'admin' => $admin,
            'token' => $token
        ];
    }

    /**
     * 退出登录
     */
    public function logout(): void
    {
        $user = Auth::user();
        
        // 删除当前token
        $user->currentAccessToken()->delete();

        // 记录日志
        $this->logService->operation('auth', 'logout', [
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'ip' => request()->ip()
        ]);
    }

    /**
     * 修改密码
     */
    public function changePassword(string $oldPassword, string $newPassword): void
    {
        $user = Auth::user();

        if (!Hash::check($oldPassword, $user->password)) {
            throw new Exception('原密码错误');
        }

        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        // 记录日志
        $this->logService->operation('auth', 'change_password', [
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'ip' => request()->ip()
        ]);
    }

    /**
     * 重置密码
     */
    public function resetPassword(string $phone, string $code): void
    {
        // 验证验证码
        if (!$this->smsService->verifyCode($phone, $code)) {
            throw new Exception('验证码错误或已过期');
        }

        $user = User::where('phone', $phone)->first();
        if (!$user) {
            throw new Exception('用户不存在');
        }

        // 重置为默认密码
        $user->update([
            'password' => Hash::make('123456')
        ]);

        // 记录日志
        $this->logService->operation('auth', 'reset_password', [
            'user_id' => $user->id,
            'phone' => $phone,
            'ip' => request()->ip()
        ]);
    }

    /**
     * 检查权限
     */
    public function checkPermission(string $permission): bool
    {
        $user = Auth::user();
        
        if ($user instanceof Admin) {
            return $user->hasPermission($permission);
        }
        
        return false;
    }
} 