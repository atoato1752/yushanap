<?php

namespace App\Services;

use App\Models\User;
use App\Models\Agent;
use Illuminate\Support\Facades\Validator;
use Exception;

class ValidationService
{
    /**
     * 验证手机号
     */
    public function validatePhone(string $phone): bool
    {
        $validator = Validator::make(['phone' => $phone], [
            'phone' => 'required|regex:/^1[3-9]\d{9}$/'
        ]);

        if ($validator->fails()) {
            throw new Exception('无效的手机号码');
        }

        return true;
    }

    /**
     * 验证身份证号
     */
    public function validateIdCard(string $idCard): bool
    {
        $validator = Validator::make(['id_card' => $idCard], [
            'id_card' => [
                'required',
                'regex:/^[1-9]\d{5}(?:18|19|20)\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[1-2]\d|3[0-1])\d{3}[\dXx]$/'
            ]
        ]);

        if ($validator->fails()) {
            throw new Exception('无效的身份证号码');
        }

        // 验证校验码
        if (!$this->checkIdCardVerifyCode($idCard)) {
            throw new Exception('身份证号码校验失败');
        }

        return true;
    }

    /**
     * 验证查询参数
     */
    public function validateQueryParams(array $data): array
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:50',
            'id_card' => 'required|string|size:18',
            'payment_type' => 'required|in:wechat,alipay,auth_code',
            'auth_code' => 'required_if:payment_type,auth_code'
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        return $validator->validated();
    }

    /**
     * 验证代理商参数
     */
    public function validateAgentParams(array $data, ?Agent $agent = null): array
    {
        $rules = [
            'username' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9_]+$/',
                $agent ? 'unique:agents,username,' . $agent->id : 'unique:agents,username'
            ],
            'name' => 'required|string|max:50',
            'password' => $agent ? 'nullable|string|min:6' : 'required|string|min:6',
            'parent_id' => 'nullable|exists:agents,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0|gte:cost_price',
            'status' => 'boolean'
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        return $validator->validated();
    }

    /**
     * 验证投诉参数
     */
    public function validateComplaintParams(array $data): array
    {
        $validator = Validator::make($data, [
            'content' => 'required|string|max:1000',
            'images.*' => 'nullable|image|max:5120' // 5MB
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        return $validator->validated();
    }

    /**
     * 验证系统配置参数
     */
    public function validateConfigParams(array $data): array
    {
        $validator = Validator::make($data, [
            'wechat_app_id' => 'required|string',
            'wechat_mch_id' => 'required|string',
            'wechat_key' => 'required|string',
            'alipay_app_id' => 'required|string',
            'alipay_private_key' => 'required|string',
            'alipay_public_key' => 'required|string',
            'auth_codes' => 'required|string',
            'service_qrcode' => 'nullable|image|max:2048',
            'user_agreement' => 'required|string',
            'privacy_policy' => 'required|string',
            'authorization_letter' => 'required|string'
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        return $validator->validated();
    }

    /**
     * 验证身份证校验码
     */
    protected function checkIdCardVerifyCode(string $idCard): bool
    {
        $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        $verify_code = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        
        $checksum = 0;
        for ($i = 0; $i < 17; $i++) {
            $checksum += intval($idCard[$i]) * $factor[$i];
        }
        
        $mod = $checksum % 11;
        $verify = $verify_code[$mod];
        
        return strtoupper($idCard[17]) === $verify;
    }

    /**
     * 验证密码强度
     */
    public function validatePasswordStrength(string $password): bool
    {
        $validator = Validator::make(['password' => $password], [
            'password' => [
                'required',
                'min:8',
                'regex:/[A-Z]/',    // 至少一个大写字母
                'regex:/[a-z]/',    // 至少一个小写字母
                'regex:/[0-9]/',    // 至少一个数字
                'regex:/[^A-Za-z0-9]/'  // 至少一个特殊字符
            ]
        ]);

        return !$validator->fails();
    }

    /**
     * 验证金额格式
     */
    public function validateAmount(float $amount): bool
    {
        $validator = Validator::make(['amount' => $amount], [
            'amount' => 'required|numeric|min:0.01|max:999999.99'
        ]);

        if ($validator->fails()) {
            throw new Exception('无效的金额');
        }

        return true;
    }
} 