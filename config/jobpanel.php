<?php

return [
    'driver' => env('QUEUE_CONNECTION', 'sync'),
    'queue' => env('REDIS_QUEUE', 'default'),
    'database' => [
        'table' => 'jobs',
    ],
    'failed' => [
        'table' => 'failed_jobs',
    ],
    'route' => [
        'domain' => null,
        'prefix' => 'admin',
        'middleware' => 'web',
    ],
    'supervisor' => [],
];
