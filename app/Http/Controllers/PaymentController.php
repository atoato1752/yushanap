<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Query;
use App\Models\Payment;
use App\Services\YushanService;
use App\Services\PaymentService;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $yushanService;

    public function __construct(PaymentService $paymentService, YushanService $yushanService)
    {
        $this->paymentService = $paymentService;
        $this->yushanService = $yushanService;
    }

    public function show(Query $query)
    {
        if ($query->payment_status === 'paid') {
            return redirect()->route('queries.show', $query);
        }

        return view('query.payment', compact('query'));
    }

    public function wechat(Query $query)
    {
        return $this->paymentService->createWechatOrder($query);
    }

    public function alipay(Query $query)
    {
        return $this->paymentService->createAlipayOrder($query);
    }

    public function authCode(Query $query, Request $request)
    {
        if (!$this->paymentService->verifyAuthCode($request->code)) {
            return response()->json(['message' => '授权码无效'], 422);
        }

        $query->update([
            'payment_type' => 'auth_code',
            'payment_status' => 'paid'
        ]);

        return response()->json([
            'message' => '支付成功',
            'redirect_url' => route('queries.show', $query)
        ]);
    }

    public function notify(Request $request, $type)
    {
        $result = $this->paymentService->handleNotify($request, $type);
        
        if ($result['success']) {
            return $result['response'];
        }

        return response($result['response'], 400);
    }

    public function status(Query $query)
    {
        return response()->json([
            'status' => $query->payment_status
        ]);
    }

    protected function handleAgentCommission($query)
    {
        // 处理代理商分成逻辑
    }
} 