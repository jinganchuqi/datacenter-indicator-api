<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

$appConfig = loadAppConfig();

return [
    'default' => [
        'host' => \Hyperf\Support\env('REDIS_HOST', get_value($appConfig, 'redis.market.host')),
        'auth' => \Hyperf\Support\env('REDIS_AUTH', get_value($appConfig, 'redis.market.auth')),
        'port' => (int)\Hyperf\Support\env('REDIS_PORT', get_value($appConfig, 'redis.market.port')),
        'db' => (int)\Hyperf\Support\env('REDIS_DB', get_value($appConfig, 'redis.market.db')),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float)\Hyperf\Support\env('REDIS_MAX_IDLE_TIME', 60),
        ],
        'enable' => false,  // 禁用 Redis 连接
    ],
    'idGenerator' => [
        'host' => \Hyperf\Support\env('REDIS_HOST', get_value($appConfig, 'redis.idGenerator.host')),
        'auth' => \Hyperf\Support\env('REDIS_AUTH', get_value($appConfig, 'redis.idGenerator.auth')),
        'port' => (int)\Hyperf\Support\env('REDIS_PORT', get_value($appConfig, 'redis.idGenerator.port')),
        'db' => (int)\Hyperf\Support\env('REDIS_DB', get_value($appConfig, 'redis.idGenerator.db')),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float)\Hyperf\Support\env('REDIS_GENERATOR_MAX_IDLE_TIME', 60),
        ],
        'enable' => false,  // 禁用 Redis 连接
    ],
];
