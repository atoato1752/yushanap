<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ComplaintUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user('admin')->isAdmin();
    }

    public function rules()
    {
        return [
            'status' => ['required', Rule::in(['pending', 'processing', 'resolved'])],
            'admin_remark' => 'nullable|string|max:1000'
        ];
    }

    public function messages()
    {
        return [
            'status.required' => '请选择状态',
            'status.in' => '状态值无效',
            'admin_remark.max' => '处理备注不能超过1000个字符'
        ];
    }
} 