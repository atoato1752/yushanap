<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemConfig;
use App\Http\Requests\Admin\SystemConfigRequest;
use Illuminate\Support\Facades\Storage;

class SystemConfigController extends Controller
{
    public function index()
    {
        $configs = SystemConfig::pluck('value', 'key_name')->toArray();
        return view('admin.system.config', compact('configs'));
    }

    public function update(SystemConfigRequest $request)
    {
        $data = $request->validated();

        // 处理客服二维码上传
        if ($request->hasFile('service_qrcode')) {
            $path = $request->file('service_qrcode')->store('public/qrcodes');
            $data['service_qrcode'] = Storage::url($path);
        }

        // 批量更新配置
        foreach ($data as $key => $value) {
            SystemConfig::updateOrCreate(
                ['key_name' => $key],
                ['value' => $value]
            );
        }

        return redirect()
            ->route('admin.system.config')
            ->with('success', '系统配置已更新');
    }
} 