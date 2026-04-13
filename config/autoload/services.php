<?php
declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-06-19
 */

$registry = [
    'protocol' => 'nacos',
    'address' => "http://" . env('NACOS_HOST', ''),
];

$options = [
    'connect_timeout' => 10.0,
    'recv_timeout' => 10.0,
    // 重试次数，默认值为 2，收包超时不进行重试。暂只支持 JsonRpcPoolTransporter
    'retry_count' => 2,
    'heartbeat' => 30,
    // 重试间隔，毫秒
    'retry_interval' => 100,
    // 当使用 JsonRpcPoolTransporter 时会用到以下配置
    'pool' => [
        'min_connections' => 1,
        'max_connections' => 32,
        'connect_timeout' => 10.0,
        'wait_timeout' => 3.0,
        'heartbeat' => -1,
        'max_idle_time' => 60.0,
    ]
];

// 服务定义
$consumerServices = [
    //'UserInfoService' => \App\Service\Contract\UserInfoContract::class,
];

return [
    'enable' => [
        'discovery' => true,
        'register' => true,
    ],
    'consumers' => value(function () use ($consumerServices, $registry, $options) {
        $consumers = [];
        foreach ($consumerServices as $name => $interface) {
            $consumers[] = [
                'name' => $name,
                'service' => $interface,
                'id' => $interface,
                'protocol' => 'jsonrpc-http',
                'load_balancer' => 'random',
                'registry' => $registry,
                // 配置项，会影响到 Packer 和 Transporter
                'options' => $options,
            ];
        }
        return $consumers;
    }),
    'providers' => [],
    'drivers' => [
        'nacos' => [
            'host' => \Hyperf\Support\env('NACOS_HOST', ''),
            'port' => 80,
            'username' => \Hyperf\Support\env('NACOS_USER', ''),
            'password' => \Hyperf\Support\env('NACOS_PASSWORD', ''),
            'guzzle' => [
                'config' => null,
            ],
            'group_name' => 'public',
            'namespace_id' => 'public',
            'heartbeat' => 5,
            'ephemeral' => true,
        ],
    ]
];
