<?php

namespace App\Http\Controllers;

use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function send(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|size:11'
        ]);

        // 检查发送频率
        $key = "sms_limit:{$request->phone}";
        if (cache()->has($key)) {
            return response()->json([
                'message' => '请稍后再试'
            ], 429);
        }

        try {
            $this->smsService->sendVerificationCode($request->phone);
            
            // 设置发送限制
            cache()->put($key, true, now()->addMinutes(1));

            return response()->json([
                'message' => '验证码已发送'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '发送失败，请稍后重试'
            ], 500);
        }
    }
} 