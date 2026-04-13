<?php

namespace App\Support;

use Hyperf\Context\ApplicationContext;
use Hyperf\Stringable\Str;
use Psr\SimpleCache\CacheInterface;
use WLib\WLog;

/**
 * Date：2023/8/31
 * Description:基础仓库层
 */
abstract class BaseRepository
{
    /**
     * @var bool
     */
    protected static bool $globalForget = false;

    /**
     * @var string
     */
    private static string $cachePrefix = "repository_cache";

    /**
     * @return CacheInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function cacheHandle(): CacheInterface
    {
        return ApplicationContext::getContainer()->get(\Psr\SimpleCache\CacheInterface::class);
    }

    /**
     * @param string $key
     * @param mixed  $callback
     * @param string $topic
     * @param bool   $forget
     * @param int    $ttl
     * @return mixed
     */
    protected static function cache(
        string $key,
        mixed $callback,
        string $topic,
        bool $forget = true,
        int $ttl = 3600
    ): mixed {
        $data = null;
        try {
            $topic = Str::snake($topic);
            $key = self::formatKey($key, $topic);
            $client = self::cacheHandle();

            if (!$forget && (!self::$globalForget)) {
                $data = $client->get($key);
            }

            if (!empty($data)) {
                return $data;
            }

            $data = self::callbackData($callback);
            $client->set($key, $data, $ttl);
        } catch (\Throwable $throwable) {
            WLog::error("数据缓存失败:{$key}:{$throwable->getMessage()}");
        }

        return $data;
    }


    /**
     * @param mixed $callback
     * @return mixed
     */
    protected static function callbackData(mixed $callback): mixed
    {
        if ($callback instanceof \Closure) {
            $data = $callback();
        } else {
            $data = $callback;
        }
        return $data;
    }

    /**
     * @param string $key
     * @param string $topic
     * @return string
     */
    protected static function formatKey(string $key, string $topic): string
    {
        $cachePrefix = self::$cachePrefix;
        $class = static::class;
        $classSet = explode("\\", $class);
        $className = end($classSet);
        $className = Str::snake($className);
        return "{$cachePrefix}:$className:{$topic}:{$key}";
    }
}
