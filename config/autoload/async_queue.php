<?php

return [
    'mq_sync_riskdata' => [
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'redis' => [
            'pool' => 'default'
        ],
        'channel' => 'mq_sync_riskdata',
        'timeout' => 2,
        'retry_seconds' => 1,
        'handle_timeout' => 60,
        'processes' => 1,
        'concurrent' => [
            'limit' => 1,
        ],
    ],
    'mq_sync_datacenter' => [
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'redis' => [
            'pool' => 'default'
        ],
        'channel' => 'mq_sync_datacenter',
        'timeout' => 2,
        'retry_seconds' => 1,
        'handle_timeout' => 60,
        'processes' => 1,
        'concurrent' => [
            'limit' => 1,
        ],
    ],
];
