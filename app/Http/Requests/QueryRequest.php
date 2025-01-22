<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QueryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:50',
            'phone' => 'required|string|size:11',
            'verification_code' => 'required|string|size:6',
            'auth_code' => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '请输入姓名',
            'name.max' => '姓名不能超过50个字符',
            'phone.required' => '请输入手机号',
            'phone.size' => '请输入正确的手机号',
            'verification_code.required' => '请输入验证码',
            'verification_code.size' => '验证码必须是6位数字'
        ];
    }
} 