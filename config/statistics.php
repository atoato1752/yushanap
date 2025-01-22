<?php

return [
    'cache' => [
        'ttl' => env('STATISTICS_CACHE_TTL', 3600),
        'prefix' => 'statistics:',
    ],

    'trends' => [
        'default_days' => 7,
        'max_days' => 30,
    ],

    'rankings' => [
        'default_limit' => 10,
        'max_limit' => 100,
    ],

    'reports' => [
        'path' => storage_path('app/reports'),
        'format' => 'Y-m-d',
        'retention_days' => 90,
    ],

    'export' => [
        'chunk_size' => 1000,
        'formats' => ['xlsx', 'csv'],
    ],
]; 