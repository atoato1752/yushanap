<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('system_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key_name')->unique();
            $table->text('value');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 插入默认配置
        DB::table('system_configs')->insert([
            [
                'key_name' => 'wechat_app_id',
                'value' => '',
                'description' => '微信支付AppID'
            ],
            [
                'key_name' => 'wechat_mch_id',
                'value' => '',
                'description' => '微信支付商户号'
            ],
            [
                'key_name' => 'wechat_key',
                'value' => '',
                'description' => '微信支付密钥'
            ],
            [
                'key_name' => 'alipay_app_id',
                'value' => '',
                'description' => '支付宝AppID'
            ],
            [
                'key_name' => 'alipay_private_key',
                'value' => '',
                'description' => '支付宝私钥'
            ],
            [
                'key_name' => 'auth_codes',
                'value' => '',
                'description' => '授权码列表'
            ],
            [
                'key_name' => 'service_qrcode',
                'value' => '',
                'description' => '客服二维码'
            ],
            [
                'key_name' => 'user_agreement',
                'value' => '用户协议内容',
                'description' => '用户协议'
            ],
            [
                'key_name' => 'privacy_policy',
                'value' => '隐私政策内容',
                'description' => '隐私政策'
            ],
            [
                'key_name' => 'authorization_letter',
                'value' => '授权书内容',
                'description' => '授权书'
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('system_configs');
    }
}; 