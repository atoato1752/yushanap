<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Query;
use App\Http\Resources\QueryResource;
use App\Http\Requests\QueryRequest;
use App\Services\SmsService;
use App\Models\User;

class QueryController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function store(QueryRequest $request)
    {
        // 验证短信验证码
        if (!$this->smsService->verifyCode($request->phone, $request->verification_code)) {
            return response()->json([
                'message' => '验证码错误'
            ], 422);
        }

        // 创建或获取用户
        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            ['name' => $request->name]
        );

        // 创建查询记录
        $query = $user->queries()->create([
            'amount' => config('payment.auth_code.price', 99)
        ]);

        return new QueryResource($query);
    }

    public function show($id)
    {
        $query = Query::with(['user', 'payment'])->findOrFail($id);
        return new QueryResource($query);
    }
} 