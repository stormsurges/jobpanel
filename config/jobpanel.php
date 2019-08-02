<?php

return [
    // 链接类型 redis、database、sync
    'driver' => env('QUEUE_CONNECTION', 'sync'),
    // 默认队列名称
    'queue' => env('REDIS_QUEUE', 'default'),

    // 队列数据表（driver = database）
    'database' => [
        'table' => 'jobs',
    ],

    'failed' => [
        // 失败队列表
        'table' => 'failed_jobs',
    ],
    // 路由配置
    'route' => [
        'domain' => null,
        'prefix' => 'admin',
        'middleware' => 'web',
    ],
    // 进程管理配置【预留】
    'supervisor' => [],
];
