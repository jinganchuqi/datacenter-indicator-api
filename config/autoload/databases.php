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
        'driver' => \Hyperf\Support\env('DB_QUERY_DRIVER', 'mysql'),
        'host' => \Hyperf\Support\env('DB_QUERY_HOST', get_value($appConfig, 'database.query.host')),
        'database' => \Hyperf\Support\env('DB_QUERY_DATABASE', get_value($appConfig, 'database.analyse.database')),
        'port' => \Hyperf\Support\env('DB_QUERY_PORT', get_value($appConfig, 'database.query.port')),
        'username' => \Hyperf\Support\env('DB_QUERY_USERNAME', get_value($appConfig, 'database.query.username')),
        'password' => \Hyperf\Support\env('DB_QUERY_PASSWORD', get_value($appConfig, 'database.query.password')),
        'charset' => \Hyperf\Support\env('DB_QUERY_CHARSET', get_value($appConfig, 'database.query.charset')),
        'collation' => \Hyperf\Support\env('DB_QUERY_COLLATION', 'utf8mb4_unicode_ci'),
        'prefix' => \Hyperf\Support\env('DB_QUERY_PREFIX', ''),
        'timezone' => \Hyperf\Support\env('DB_QUERY_TIMEZONE', get_value($appConfig, 'database.analyse.timezone')),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 40,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float)\Hyperf\Support\env('DB_MAX_IDLE_TIME', 60),
        ],
        'commands' => [],
    ],
    'clickhouse' => [
        'driver' => \Hyperf\Support\env('DB_QUERY_DRIVER', 'mysql'),
        'host' => \Hyperf\Support\env('CLICKHOUSE_HOST', get_value($appConfig, 'database.query.host')),
        'database' => \Hyperf\Support\env('CLICKHOUSE_DATABASE', get_value($appConfig, 'database.analyse.database')),
        'port' => \Hyperf\Support\env('CLICKHOUSE_PORT', get_value($appConfig, 'database.query.port')),
        'username' => \Hyperf\Support\env('CLICKHOUSE_USERNAME', get_value($appConfig, 'database.query.username')),
        'password' => \Hyperf\Support\env('CLICKHOUSE_PASSWORD', get_value($appConfig, 'database.query.password')),
        'charset' => \Hyperf\Support\env('CLICKHOUSE_CHARSET', get_value($appConfig, 'database.query.charset')),
        'collation' => \Hyperf\Support\env('CLICKHOUSE_COLLATION', 'utf8mb4_unicode_ci'),
        'prefix' => \Hyperf\Support\env('DB_QUERY_PREFIX', ''),
        'timezone' => \Hyperf\Support\env('DB_QUERY_TIMEZONE', get_value($appConfig, 'database.analyse.timezone')),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 40,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float)\Hyperf\Support\env('DB_MAX_IDLE_TIME', 60),
        ],
        'commands' => [],
        'options' => [
            PDO::ATTR_EMULATE_PREPARES => true,
        ],
    ],
];
