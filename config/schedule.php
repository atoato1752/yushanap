<?php

return [
    'log_cleanup' => [
        'days' => env('LOG_CLEANUP_DAYS', 30),
    ],

    'notification_cleanup' => [
        'days' => env('NOTIFICATION_CLEANUP_DAYS', 30),
    ],

    'earnings_settlement' => [
        'auto_settle' => env('AUTO_SETTLE_EARNINGS', true),
        'min_amount' => env('MIN_SETTLEMENT_AMOUNT', 100),
    ],

    'order_timeout' => [
        'minutes' => env('ORDER_TIMEOUT_MINUTES', 30),
        'auto_cancel' => env('AUTO_CANCEL_TIMEOUT_ORDERS', true),
    ],

    'daily_report' => [
        'time' => env('DAILY_REPORT_TIME', '01:00'),
        'recipients' => explode(',', env('DAILY_REPORT_RECIPIENTS', '')),
    ],
]; 