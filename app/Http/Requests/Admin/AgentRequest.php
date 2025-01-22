<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AgentRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user('admin')->isAdmin();
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:50',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0|gt:cost_price',
            'status' => 'boolean'
        ];

        // 创建时需要验证用户名
        if ($this->isMethod('post')) {
            $rules['username'] = 'required|string|unique:agents|max:20';
            $rules['parent_id'] = 'nullable|exists:agents,id';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'username.required' => '请输入用户名',
            'username.unique' => '用户名已存在',
            'username.max' => '用户名不能超过20个字符',
            'name.required' => '请输入姓名',
            'name.max' => '姓名不能超过50个字符',
            'cost_price.required' => '请输入成本价',
            'cost_price.numeric' => '成本价必须是数字',
            'cost_price.min' => '成本价不能小于0',
            'selling_price.required' => '请输入销售价',
            'selling_price.numeric' => '销售价必须是数字',
            'selling_price.min' => '销售价不能小于0',
            'selling_price.gt' => '销售价必须大于成本价',
            'parent_id.exists' => '上级代理不存在'
        ];
    }
} 