<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Query;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    protected $config;
    protected $logService;

    public function __construct(LogService $logService)
    {
        $this->config = config('payment');
        $this->logService = $logService;
    }

    public function verifyAuthCode(string $code): bool
    {
        $authCodes = explode("\n", config('system.auth_codes', ''));
        return in_array($code, array_map('trim', $authCodes));
    }

    /**
     * 创建支付订单
     */
    public function create(Query $query, string $paymentType): Payment
    {
        return DB::transaction(function () use ($query, $paymentType) {
            // 检查订单状态
            if ($query->payment_status === 'paid') {
                throw new Exception('该查询已支付');
            }

            // 创建支付记录
            $payment = new Payment([
                'payment_no' => $this->generatePaymentNo(),
                'payment_type' => $paymentType,
                'amount' => $query->amount,
                'status' => 'pending'
            ]);

            $payment->query()->associate($query);
            $payment->save();

            // 更新查询状态
            $query->update([
                'payment_status' => 'pending',
                'payment_id' => $payment->id
            ]);

            // 记录日志
            $this->logService->payment('create', [
                'query_id' => $query->id,
                'payment_id' => $payment->id,
                'type' => $paymentType,
                'amount' => $query->amount
            ]);

            return $payment;
        });
    }

    /**
     * 处理支付成功
     */
    public function handleSuccess(Payment $payment, array $data): void
    {
        DB::transaction(function () use ($payment, $data) {
            // 更新支付记录
            $payment->update([
                'status' => 'success',
                'paid_at' => now(),
                'transaction_id' => $data['transaction_id'] ?? null,
                'payment_data' => $data
            ]);

            // 更新查询状态
            $payment->query->update([
                'payment_status' => 'paid',
                'status' => 'pending'
            ]);

            // 如果是代理商查询，处理代理商收益
            if ($payment->query->agent) {
                $this->handleAgentEarning($payment);
            }

            // 记录日志
            $this->logService->payment('success', [
                'query_id' => $payment->query_id,
                'payment_id' => $payment->id,
                'transaction_id' => $data['transaction_id'] ?? null
            ]);
        });
    }

    /**
     * 处理支付失败
     */
    public function handleFailure(Payment $payment, string $reason): void
    {
        DB::transaction(function () use ($payment, $reason) {
            // 更新支付记录
            $payment->update([
                'status' => 'failed',
                'error_message' => $reason
            ]);

            // 更新查询状态
            $payment->query->update([
                'payment_status' => 'failed',
                'status' => 'cancelled'
            ]);

            // 记录日志
            $this->logService->payment('failure', [
                'query_id' => $payment->query_id,
                'payment_id' => $payment->id,
                'reason' => $reason
            ]);
        });
    }

    /**
     * 处理退款
     */
    public function handleRefund(Payment $payment, string $reason): void
    {
        DB::transaction(function () use ($payment, $reason) {
            // 更新支付记录
            $payment->update([
                'status' => 'refunded',
                'refunded_at' => now(),
                'refund_reason' => $reason
            ]);

            // 更新查询状态
            $payment->query->update([
                'payment_status' => 'refunded',
                'status' => 'cancelled'
            ]);

            // 如果是代理商查询，处理代理商退款
            if ($payment->query->agent) {
                $this->handleAgentRefund($payment);
            }

            // 记录日志
            $this->logService->payment('refund', [
                'query_id' => $payment->query_id,
                'payment_id' => $payment->id,
                'reason' => $reason
            ]);
        });
    }

    /**
     * 处理代理商收益
     */
    protected function handleAgentEarning(Payment $payment): void
    {
        $query = $payment->query;
        $agent = $query->agent;

        // 计算收益金额
        $amount = $payment->amount - $agent->cost_price;

        // 创建收益记录
        $earning = $agent->earnings()->create([
            'query_id' => $query->id,
            'amount' => $amount,
            'status' => 'pending'
        ]);

        // 增加代理商余额
        $agent->increment('balance', $amount);

        // 记录日志
        $this->logService->agent('earning', [
            'agent_id' => $agent->id,
            'query_id' => $query->id,
            'amount' => $amount
        ]);
    }

    /**
     * 处理代理商退款
     */
    protected function handleAgentRefund(Payment $payment): void
    {
        $query = $payment->query;
        $agent = $query->agent;
        $earning = $agent->earnings()->where('query_id', $query->id)->first();

        if ($earning) {
            // 扣除代理商余额
            $agent->decrement('balance', $earning->amount);

            // 更新收益记录
            $earning->update([
                'status' => 'refunded',
                'refunded_at' => now()
            ]);

            // 记录日志
            $this->logService->agent('refund', [
                'agent_id' => $agent->id,
                'query_id' => $query->id,
                'amount' => $earning->amount
            ]);
        }
    }

    /**
     * 生成支付单号
     */
    protected function generatePaymentNo(): string
    {
        return date('YmdHis') . Str::random(8);
    }

    /**
     * 验证支付通知签名
     */
    public function verifyNotifySign(array $data, string $type): bool
    {
        $method = 'verify' . ucfirst($type) . 'Sign';
        return method_exists($this, $method) ? $this->$method($data) : false;
    }

    /**
     * 验证微信支付签名
     */
    protected function verifyWechatSign(array $data): bool
    {
        // 实现微信支付签名验证逻辑
        return true;
    }

    /**
     * 验证支付宝签名
     */
    protected function verifyAlipaySign(array $data): bool
    {
        // 实现支付宝签名验证逻辑
        return true;
    }

    // 其他辅助方法...
} 