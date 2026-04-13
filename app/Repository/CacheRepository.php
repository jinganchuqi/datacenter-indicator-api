<?php

namespace App\Repository;

use App\Support\BaseRepository;
use Hyperf\Context\ApplicationContext;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use WLib\WLog;

/**
 * Date：2023/9/25
 * Description: 公用缓存
 */
class CacheRepository extends BaseRepository
{
    /**
     * 自增
     * @param     $key
     * @param int $ttl
     * @return false|mixed|\Redis|string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \RedisException
     */
    public static function incr($key, int $ttl = 86400): mixed
    {
        $key = self::formatKey($key, 'base');
        $client = self::redisClient();

        $ok = $client->set($key, 1, [
            'nx',
            'ex' => $ttl,
        ]);

        if (!$ok) {
            $client->incr($key);
        }

        return $client->get($key);
    }

    /**
     * @param $key
     * @return false|mixed|\Redis|string|null
     */
    public static function get($key)
    {
        try {
            $key = self::formatKey($key, 'base');
            $client = self::redisClient();
            return $client->get($key);
        } catch (\Throwable $throwable) {
            WLog::error("缓存获取:$key:{$throwable->getMessage()}");
            return null;
        }
    }

    /**
     * @param $key
     * @return \Redis|int|bool
     */
    public static function exist($key): \Redis|int|bool
    {
        try {
            $key = self::formatKey($key, 'base');
            $client = self::redisClient();
            $client->exists($key);
            return $client->exists($key);
        } catch (\Throwable $throwable) {
            return false;
        }
    }

    /**
     * @param            $key
     * @param string|int $value
     * @param int        $ttl
     * @return void
     */
    public static function set($key, string|int $value, int $ttl = 86400): void
    {
        try {
            $key = self::formatKey($key, 'base');
            $client = self::redisClient();
            $client->set($key, $value, $ttl);
        } catch (\Throwable $throwable) {
            WLog::error("缓存设置:$key:{$throwable->getMessage()}");
        }
    }

    /**
     * @return Redis|mixed
     */
    public static function redisClient(): mixed
    {
        $redis = ApplicationContext::getContainer()->get(Redis::class);
        $redis->select(\Hyperf\Config\config('redis.default.db'));
        return $redis;
    }
}
