<?php

namespace App\Common\Log;

use function Hyperf\Config\config;

use Hyperf\Collection\Arr;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @method static debug(string $message, array $context = [], string $channel = '')
 * @method static info(string $message, array $context = [], string $channel = '')
 * @method static notice(string $message, array $context = [], string $channel = '')
 * @method static warning(string $message, array $context = [], string $channel = '')
 * @method static error(string $message, array $context = [], string $channel = '')
 * @method static critical(string $message, array $context = [], string $channel = '')
 * @method static alert(string $message, array $context = [], string $channel = '')
 * @method static emergency(string $message, array $context = [], string $channel = '')
 */
class AppLog
{
    /**
     * @param string $name
     * @param array $arguments
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $message = Arr::get($arguments, 0, '');
        $context = Arr::get($arguments, 1, []);
        $channel = Arr::get($arguments, 2, config('app_name'));
        StdoutLogger::get($channel)->$name($message, $context);
    }
}