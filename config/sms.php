<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 默认短信服务商
    |--------------------------------------------------------------------------
    |
    | 支持: "aliyun", "tencent"
    |
    */
    'default' => env('SMS_DRIVER', 'aliyun'),

    /*
    |--------------------------------------------------------------------------
    | 短信服务商配置
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'aliyun' => [
            'access_key_id' => env('ALIYUN_ACCESS_KEY_ID'),
            'access_key_secret' => env('ALIYUN_ACCESS_KEY_SECRET'),
            'sign_name' => env('ALIYUN_SMS_SIGN_NAME'),
            'templates' => [
                'verify' => env('ALIYUN_SMS_TEMPLATE_VERIFY'),
                'notify' => env('ALIYUN_SMS_TEMPLATE_NOTIFY'),
            ],
        ],

        'tencent' => [
            'app_id' => env('TENCENT_SMS_APP_ID'),
            'app_key' => env('TENCENT_SMS_APP_KEY'),
            'sign_name' => env('TENCENT_SMS_SIGN_NAME'),
            'templates' => [
                'verification' => env('TENCENT_SMS_TEMPLATE_VERIFICATION'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 验证码配置
    |--------------------------------------------------------------------------
    */
    'verification' => [
        // 验证码长度
        'length' => 6,

        // 验证码有效期（分钟）
        'expire' => 5,

        // 同一手机号发送间隔（秒）
        'interval' => 60,

        // 同一手机号每日最大发送次数
        'daily_limit' => 10,

        // 同一IP每日最大发送次数
        'ip_limit' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | 缓存配置
    |--------------------------------------------------------------------------
    */
    'cache' => [
        // 验证码缓存前缀
        'prefix' => 'sms_code:',

        // 发送记录缓存前缀
        'record_prefix' => 'sms_record:',

        // 发送记录缓存时间（秒）
        'record_ttl' => 86400,
    ],

    // API配置
    'api_url' => env('SMS_API_URL', 'https://api.sms.com'),
    'api_key' => env('SMS_API_KEY'),
    'api_secret' => env('SMS_API_SECRET'),

    // 模板配置
    'templates' => [
        'verification' => env('SMS_TEMPLATE_VERIFICATION'),
        'query_completed' => env('SMS_TEMPLATE_QUERY_COMPLETED'),
        'complaint_handled' => env('SMS_TEMPLATE_COMPLAINT_HANDLED'),
        'payment_succeeded' => env('SMS_TEMPLATE_PAYMENT_SUCCEEDED'),
    ],

    // 发送限制
    'limits' => [
        'minute' => env('SMS_LIMIT_MINUTE', 1),
        'hour' => env('SMS_LIMIT_HOUR', 5),
        'day' => env('SMS_LIMIT_DAY', 10)
    ],

    // 验证码配置
    'code_length' => env('SMS_CODE_LENGTH', 6),
    'code_expires' => env('SMS_CODE_EXPIRES', 5), // 分钟
    'code_prefix' => 'sms:code:',

    // 重试配置
    'retry_times' => env('SMS_RETRY_TIMES', 3),
    'retry_delay' => env('SMS_RETRY_DELAY', 1),

    // 日志配置
    'log_channel' => env('SMS_LOG_CHANNEL', 'sms'),
    'log_level' => env('SMS_LOG_LEVEL', 'info'),

    // 测试模式
    'test_mode' => env('SMS_TEST_MODE', false),
    'test_code' => env('SMS_TEST_CODE', '123456'),
    'test_phones' => explode(',', env('SMS_TEST_PHONES', '')),

    'verify_code' => [
        'length' => 6,
        'expire' => 300,
        'limit' => [
            'day' => 10,
            'hour' => 5,
            'minute' => 1,
        ],
    ],
]; 