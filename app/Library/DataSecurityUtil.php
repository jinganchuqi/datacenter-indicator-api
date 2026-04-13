<?php

namespace App\Library;

use App\Common\Guzzle\Client;
use App\Exception\AppException;
use App\Repository\CacheRepository;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Date：2023/10/10
 * Description: 数据解加密
 *
 */
class DataSecurityUtil
{
    /**
     * @var string
     */
    protected static string $url = "http://172.31.32.2:8080";

    /**
     * @param string $input
     * @return string|null
     */
    public static function toHash(string|null $input): ?string
    {
        try {
            if (empty($input)) {
                return null;
            }
            $result = self::get($input);
            if (!empty($result)) {
                return $result;
            }
            $json = self::request("/v2t", [$input]);
            return arr_get($json, "tokens.0");
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    /**
     * @param array $inputs
     * @return array|null
     */
    public static function toHashBatch(array $inputs): ?array
    {
        try {
            if (empty($input)) {
                return null;
            }
            $json = self::request("/v2t", $inputs);
            return arr_get($json, "tokens");
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    /**
     * @throws AppException
     * @throws GuzzleException
     */
    public static function request($path, array $data = [])
    {
        $http = (new Client(self::$url));
        if (!empty($data)) {
            $http->post($path, $data);
        } else {
            $http->get($path);
        }

        $response = $http->responseBody;
        $json = json_decode($response, true);
        if (empty($json)) {
            throw new AppException("请求失败");
        }

        return $json;
    }

    /**
     * @return \Hyperf\Redis\Redis|mixed
     */
    protected static function getRedis(): mixed
    {
        try {
            return CacheRepository::redisClient();
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    public static function get($key)
    {
        $key = "datasecurity:{$key}";
        try {
            $redis = self::getRedis();
            if (empty($redis)) {
                return null;
            }
            return $redis->get($key);
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    /**
     * @param $key
     * @param $val
     * @return true|null
     */
    public static function set($key, $val): ?bool
    {
        $key = "datasecurity:{$key}";
        try {
            $redis = self::getRedis();
            if (empty($redis)) {
                return null;
            }
            $redis->set($key, $val, ['EX' => 3600, 'NX']);
            return true;
        } catch (\Throwable $throwable) {
            return null;
        }
    }
}