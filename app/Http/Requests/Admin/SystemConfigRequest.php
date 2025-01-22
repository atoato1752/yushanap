<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SystemConfigRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user('admin')->isAdmin();
    }

    public function rules()
    {
        return [
            'wechat_app_id' => 'required|string',
            'wechat_mch_id' => 'required|string',
            'wechat_key' => 'required|string',
            'alipay_app_id' => 'required|string',
            'alipay_private_key' => 'required|string',
            'auth_codes' => 'required|string',
            'service_qrcode' => 'nullable|image|max:1024',
            'user_agreement' => 'required|string',
            'privacy_policy' => 'required|string',
            'authorization_letter' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'wechat_app_id.required' => '请输入微信支付AppID',
            'wechat_mch_id.required' => '请输入微信支付商户号',
            'wechat_key.required' => '请输入微信支付密钥',
            'alipay_app_id.required' => '请输入支付宝AppID',
            'alipay_private_key.required' => '请输入支付宝私钥',
            'auth_codes.required' => '请输入授权码列表',
            'service_qrcode.image' => '客服二维码必须是图片',
            'service_qrcode.max' => '客服二维码不能超过1MB',
            'user_agreement.required' => '请输入用户协议',
            'privacy_policy.required' => '请输入隐私政策',
            'authorization_letter.required' => '请输入授权书'
        ];
    }
} 