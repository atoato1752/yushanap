<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class SmsService
{
    protected $config;
    protected $logService;

    public function __construct(LogService $logService)
    {
        $this->config = config('sms');
        $this->logService = $logService;
    }

    /**
     * 发送验证码
     */
    public function sendVerificationCode(string $phone): string
    {
        // 检查发送频率限制
        $this->checkRateLimit($phone);

        // 生成验证码
        $code = $this->generateCode();

        // 发送短信
        $this->send($phone, $this->config['templates']['verification'], [
            'code' => $code
        ]);

        // 缓存验证码
        $this->storeCode($phone, $code);

        // 记录日志
        $this->logService->operation('sms', 'send_code', [
            'phone' => $phone,
            'code' => $code,
            'ip' => request()->ip()
        ]);

        return $code;
    }

    /**
     * 验证验证码
     */
    public function verifyCode(string $phone, string $code): bool
    {
        $key = "sms:code:{$phone}";
        $storedCode = Cache::get($key);

        if (!$storedCode || $storedCode !== $code) {
            return false;
        }

        // 验证成功后删除缓存
        Cache::forget($key);

        return true;
    }

    /**
     * 发送通知短信
     */
    public function sendNotification(string $phone, string $template, array $data = []): void
    {
        $this->send($phone, $template, $data);

        // 记录日志
        $this->logService->operation('sms', 'send_notification', [
            'phone' => $phone,
            'template' => $template,
            'data' => $data,
            'ip' => request()->ip()
        ]);
    }

    /**
     * 发送短信
     */
    protected function send(string $phone, string $template, array $data = []): void
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_key']
            ])->post($this->config['api_url'], [
                'phone' => $phone,
                'template' => $template,
                'data' => $data
            ]);

            if (!$response->successful()) {
                throw new Exception('短信发送失败：' . $response->body());
            }

            $result = $response->json();
            if (!$result['success']) {
                throw new Exception('短信发送失败：' . $result['message']);
            }
        } catch (Exception $e) {
            $this->logService->error($e, [
                'phone' => $phone,
                'template' => $template,
                'data' => $data
            ]);
            throw new Exception('短信发送失败，请稍后重试');
        }
    }

    /**
     * 检查发送频率限制
     */
    protected function checkRateLimit(string $phone): void
    {
        // 检查分钟限制
        $minuteKey = "sms:limit:minute:{$phone}";
        $minuteCount = Cache::get($minuteKey, 0);
        
        if ($minuteCount >= $this->config['limits']['minute']) {
            throw new Exception('发送太频繁，请稍后再试');
        }

        // 检查小时限制
        $hourKey = "sms:limit:hour:{$phone}";
        $hourCount = Cache::get($hourKey, 0);
        
        if ($hourCount >= $this->config['limits']['hour']) {
            throw new Exception('已达到小时发送限制');
        }

        // 检查天限制
        $dayKey = "sms:limit:day:{$phone}";
        $dayCount = Cache::get($dayKey, 0);
        
        if ($dayCount >= $this->config['limits']['day']) {
            throw new Exception('已达到今日发送限制');
        }

        // 更新计数器
        Cache::increment($minuteKey);
        Cache::increment($hourKey);
        Cache::increment($dayKey);

        // 设置过期时间
        if ($minuteCount === 0) {
            Cache::put($minuteKey, 1, now()->addMinute());
        }
        if ($hourCount === 0) {
            Cache::put($hourKey, 1, now()->addHour());
        }
        if ($dayCount === 0) {
            Cache::put($dayKey, 1, now()->endOfDay());
        }
    }

    /**
     * 生成验证码
     */
    protected function generateCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    /**
     * 存储验证码
     */
    protected function storeCode(string $phone, string $code): void
    {
        $key = "sms:code:{$phone}";
        Cache::put($key, $code, now()->addMinutes($this->config['code_expires']));
    }
} 