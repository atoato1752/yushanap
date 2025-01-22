<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Exception;

class SystemService
{
    /**
     * 获取系统配置
     */
    public function getConfigs(): array
    {
        return Cache::remember('system_configs', 3600, function () {
            return [
                // 支付配置
                'wechat_app_id' => config('payment.wechat.app_id'),
                'wechat_mch_id' => config('payment.wechat.mch_id'),
                'wechat_key' => config('payment.wechat.key'),
                'alipay_app_id' => config('payment.alipay.app_id'),
                'alipay_private_key' => config('payment.alipay.private_key'),
                'alipay_public_key' => config('payment.alipay.public_key'),

                // 授权码配置
                'auth_codes' => $this->getAuthCodes(),

                // 客服配置
                'service_qrcode' => $this->getServiceQrcode(),

                // 协议配置
                'user_agreement' => $this->getAgreement('user_agreement'),
                'privacy_policy' => $this->getAgreement('privacy_policy'),
                'authorization_letter' => $this->getAgreement('authorization_letter'),
            ];
        });
    }

    /**
     * 更新系统配置
     */
    public function updateConfigs(array $data): void
    {
        DB::transaction(function () use ($data) {
            // 更新支付配置
            $this->updatePaymentConfigs($data);

            // 更新授权码
            if (isset($data['auth_codes'])) {
                $this->updateAuthCodes($data['auth_codes']);
            }

            // 更新客服二维码
            if (isset($data['service_qrcode'])) {
                $this->updateServiceQrcode($data['service_qrcode']);
            }

            // 更新协议
            $this->updateAgreements($data);

            // 清除缓存
            Cache::forget('system_configs');
        });
    }

    /**
     * 更新支付配置
     */
    protected function updatePaymentConfigs(array $data): void
    {
        $envData = [];

        // 微信支付配置
        if (isset($data['wechat_app_id'])) {
            $envData['WECHAT_PAY_APP_ID'] = $data['wechat_app_id'];
        }
        if (isset($data['wechat_mch_id'])) {
            $envData['WECHAT_PAY_MCH_ID'] = $data['wechat_mch_id'];
        }
        if (isset($data['wechat_key'])) {
            $envData['WECHAT_PAY_KEY'] = $data['wechat_key'];
        }

        // 支付宝配置
        if (isset($data['alipay_app_id'])) {
            $envData['ALIPAY_APP_ID'] = $data['alipay_app_id'];
        }
        if (isset($data['alipay_private_key'])) {
            $envData['ALIPAY_PRIVATE_KEY'] = $data['alipay_private_key'];
        }
        if (isset($data['alipay_public_key'])) {
            $envData['ALIPAY_PUBLIC_KEY'] = $data['alipay_public_key'];
        }

        if (!empty($envData)) {
            $this->updateEnvFile($envData);
        }
    }

    /**
     * 更新授权码
     */
    protected function updateAuthCodes(string $codes): void
    {
        $codes = array_filter(explode("\n", $codes));
        $codes = array_map('trim', $codes);
        Storage::put('auth_codes.txt', implode("\n", $codes));
    }

    /**
     * 更新客服二维码
     */
    protected function updateServiceQrcode($file): void
    {
        if ($file && $file->isValid()) {
            $path = $file->store('qrcodes', 'public');
            Storage::delete('service_qrcode.txt');
            Storage::put('service_qrcode.txt', $path);
        }
    }

    /**
     * 更新协议
     */
    protected function updateAgreements(array $data): void
    {
        $agreements = [
            'user_agreement',
            'privacy_policy',
            'authorization_letter'
        ];

        foreach ($agreements as $agreement) {
            if (isset($data[$agreement])) {
                Storage::put("agreements/{$agreement}.html", $data[$agreement]);
            }
        }
    }

    /**
     * 获取授权码列表
     */
    protected function getAuthCodes(): string
    {
        return Storage::exists('auth_codes.txt') 
            ? Storage::get('auth_codes.txt') 
            : '';
    }

    /**
     * 获取客服二维码
     */
    protected function getServiceQrcode(): ?string
    {
        if (!Storage::exists('service_qrcode.txt')) {
            return null;
        }
        $path = Storage::get('service_qrcode.txt');
        return Storage::exists($path) ? Storage::url($path) : null;
    }

    /**
     * 获取协议内容
     */
    protected function getAgreement(string $type): string
    {
        $path = "agreements/{$type}.html";
        return Storage::exists($path) ? Storage::get($path) : '';
    }

    /**
     * 更新环境变量文件
     */
    protected function updateEnvFile(array $data): void
    {
        $envFile = base_path('.env');
        $content = file_get_contents($envFile);

        foreach ($data as $key => $value) {
            // 转义特殊字符
            $value = str_replace('"', '\\"', $value);
            
            if (str_contains($content, $key . '=')) {
                // 更新已存在的值
                $content = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}=\"{$value}\"",
                    $content
                );
            } else {
                // 添加新值
                $content .= "\n{$key}=\"{$value}\"";
            }
        }

        file_put_contents($envFile, $content);
    }
} 