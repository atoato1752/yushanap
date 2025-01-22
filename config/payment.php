<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 支付配置
    |--------------------------------------------------------------------------
    */
    'wechat' => [
        'app_id' => env('WECHAT_PAY_APP_ID'),
        'mch_id' => env('WECHAT_PAY_MCH_ID'),
        'key' => env('WECHAT_PAY_KEY'),
        'cert_path' => env('WECHAT_PAY_CERT_PATH'),
        'key_path' => env('WECHAT_PAY_KEY_PATH'),
        'notify_url' => env('APP_URL') . '/payments/notify/wechat',
    ],

    'alipay' => [
        'app_id' => env('ALIPAY_APP_ID'),
        'private_key' => env('ALIPAY_PRIVATE_KEY'),
        'public_key' => env('ALIPAY_PUBLIC_KEY'),
        'notify_url' => env('APP_URL') . '/payments/notify/alipay',
        'return_url' => env('APP_URL') . '/payments/return/alipay',
    ],

    /*
    |--------------------------------------------------------------------------
    | 授权码配置
    |--------------------------------------------------------------------------
    */
    'auth_code' => [
        // 授权码优惠价格
        'price' => 49,
    ],

    /*
    |--------------------------------------------------------------------------
    | 支付超时配置
    |--------------------------------------------------------------------------
    */
    'timeout' => [
        // 支付超时时间（分钟）
        'minutes' => 30,

        // 支付超时自动关闭
        'auto_close' => true,
    ],
]; 