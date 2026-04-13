<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Hyperf\Kafka\Constants\KafkaStrategy;

return [
    'event' => [
        'enable' => false,
        'connect_timeout' => 30,
        'send_timeout' => 30,
        'recv_timeout' => 30,
        'client_id' => str_replace('-', '_', \Hyperf\Support\env('APP_NAME')),
        'max_write_attempts' => 3,
        'bootstrap_servers' => [
            'kafka:9092',
        ],
        'acks' => -1,
        'producer_id' => -1,
        'producer_epoch' => -1,
        'partition_leader_epoch' => -1,
        'interval' => 4,
        'session_timeout' => 30,
        'rebalance_timeout' => 60,
        'replica_id' => -1,
        'rack_id' => '',
        'group_retry' => 5,
        'group_retry_sleep' => 1,
        'group_heartbeat' => 3,
        'offset_retry' => 5,
        'auto_create_topic' => true,
        'partition_assignment_strategy' => KafkaStrategy::RANGE_ASSIGNOR,
        'sasl' => [],
        'ssl' => [],
        'client' => \longlang\phpkafka\Client\SwooleClient::class,
        'socket' => \longlang\phpkafka\Socket\SwooleSocket::class,
        'timer' => \longlang\phpkafka\Timer\SwooleTimer::class,
        'consume_timeout' => 600,
    ],
];
