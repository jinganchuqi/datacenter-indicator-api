<?php

namespace App\Common\Redis;

use Hyperf\Redis\Redis;
use Hyperf\Context\ApplicationContext;
use Hyperf\Redis\RedisFactory as BaseRedisFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class RedisFactory
{
    /**
     * @param string $poolName
     * @return Redis
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function connection(string $poolName = 'default'): Redis
    {
        return ApplicationContext::getContainer()->get(BaseRedisFactory::class)->get($poolName);
    }
}