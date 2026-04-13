<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

//use Hyperf\ConfigCenter\Mode;
use function Hyperf\Support\env;

return [
    'enable' => (bool)env('CONFIG_CENTER_ENABLE', false),
    'driver' => env('CONFIG_CENTER_DRIVER', 'etcd'),
    //'mode' => env('CONFIG_CENTER_MODE', Mode::PROCESS),
    'drivers' => [
        'etcd' => [
            'driver' => Hyperf\ConfigEtcd\EtcdDriver::class,
            'packer' => Hyperf\Codec\Packer\JsonPacker::class,
            'namespaces' => [
                '/application',
            ],
            'interval' => 5,
            'client' => [
                'uri' => env('CONFIG_CENTER_ETCD_URI', 'http://127.0.0.1:2379'),
                'version' => 'v3beta',
                'options' => [
                    'timeout' => 10,
                ],
            ],
        ],
    ],
];
