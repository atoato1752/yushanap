<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Query;
use App\Services\SmsService;
use App\Services\PaymentService;
use App\Http\Requests\QueryRequest;

class QueryController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function index()
    {
        return view('query.index');
    }

    public function sendSmsCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|regex:/^1[3-9]\d{9}$/'
        ]);

        $code = $this->smsService->sendVerificationCode($request->phone);

        return response()->json(['message' => '验证码已发送']);
    }

    public function query(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'phone' => 'required|regex:/^1[3-9]\d{9}$/',
            'sms_code' => 'required|string',
            'agreement' => 'required|accepted'
        ]);

        // 验证短信验证码
        if (!$this->smsService->verifyCode($request->phone, $request->sms_code)) {
            return response()->json(['error' => '验证码错误'], 422);
        }

        // 创建用户记录
        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone
        ]);

        // 创建查询记录
        $query = Query::create([
            'user_id' => $user->id,
            'payment_status' => 'pending'
        ]);

        return response()->json([
            'query_id' => $query->id,
            'payment_methods' => [
                'auth_code' => route('payment.auth_code', $query->id),
                'wechat' => route('payment.wechat', $query->id),
                'alipay' => route('payment.alipay', $query->id)
            ]
        ]);
    }

    public function store(QueryRequest $request)
    {
        // 验证短信验证码
        if (!$this->smsService->verifyCode($request->phone, $request->verification_code)) {
            return back()->with('error', '验证码错误');
        }

        // 创建或获取用户
        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            ['name' => $request->name]
        );

        // 计算查询费用
        $amount = 99; // 默认价格
        if ($request->auth_code) {
            // 验证授权码并获取优惠价格
            $amount = config('payment.auth_code.price', 99);
        }

        // 创建查询记录
        $query = $user->queries()->create([
            'amount' => $amount
        ]);

        return redirect()->route('payments.show', $query);
    }

    public function show(Query $query)
    {
        if ($query->payment_status !== 'paid') {
            return redirect()->route('payments.show', $query);
        }

        return view('query.show', compact('query'));
    }
} 