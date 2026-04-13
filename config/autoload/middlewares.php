<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */


use App\Middleware\TimezoneMiddleware;

return [
    'http' => [
        // TimezoneMiddleware::class,// 已在入口处统一处理
    ],
];
