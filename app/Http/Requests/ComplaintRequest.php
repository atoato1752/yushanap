<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComplaintRequest extends FormRequest
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
            'content' => 'required|string|max:1000',
            'query_id' => 'required|exists:queries,id'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '请输入姓名',
            'name.max' => '姓名不能超过50个字符',
            'phone.required' => '请输入手机号',
            'phone.size' => '请输入正确的手机号',
            'content.required' => '请输入投诉内容',
            'content.max' => '投诉内容不能超过1000个字符',
            'query_id.required' => '查询记录ID不能为空',
            'query_id.exists' => '查询记录不存在'
        ];
    }
} 