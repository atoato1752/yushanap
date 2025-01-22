<?php

return [
    // API配置
    'api' => [
        'url' => env('YUSHAN_API_URL', 'https://api.example.com'),
        'key' => env('YUSHAN_API_KEY'),
        'secret' => env('YUSHAN_API_SECRET'),
    ],

    // 查询配置
    'query' => [
        'timeout' => env('YUSHAN_QUERY_TIMEOUT', 30),
        'retry_times' => env('YUSHAN_RETRY_TIMES', 3),
        'retry_delay' => env('YUSHAN_RETRY_DELAY', 5),
    ],

    // 报告配置
    'report' => [
        'expire_days' => env('YUSHAN_REPORT_EXPIRE_DAYS', 7),
        'storage_disk' => env('YUSHAN_REPORT_STORAGE', 'local'),
        'storage_path' => env('YUSHAN_REPORT_PATH', 'reports'),
    ],

    // 查询限制
    'daily_limit' => env('YUSHAN_DAILY_LIMIT', 5),
    'concurrent_limit' => env('YUSHAN_CONCURRENT_LIMIT', 10),

    // 回调配置
    'callback_url' => env('YUSHAN_CALLBACK_URL'),
    'callback_secret' => env('YUSHAN_CALLBACK_SECRET'),

    // 缓存配置
    'cache_ttl' => env('YUSHAN_CACHE_TTL', 3600),
    'cache_prefix' => 'yushan:',

    // 报告配置
    'report_format' => env('YUSHAN_REPORT_FORMAT', 'pdf'),
    'report_retention_days' => env('YUSHAN_REPORT_RETENTION_DAYS', 90),

    // 错误处理
    'error_codes' => [
        '1001' => '系统错误',
        '1002' => '参数错误',
        '1003' => '认证失败',
        '1004' => '余额不足',
        '1005' => '查询次数超限',
        '1006' => '并发请求超限',
        // ... 其他错误码
    ],
]; 