<?php

namespace App\Util;

use App\Exception\AppException;
use App\Support\Trait\HttpTrait;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Date：2023/10/10
 * Description: 数据解加密
 *
 */
class DataSecurityUtil
{
    use HttpTrait;

    /**
     * @var string
     */
    protected static string $url = "http://identity.api.com:9522";

    /**
     * 转换为hash
     * @throws AppException
     * @throws GuzzleException
     */
    public static function toHash(string|int $input)
    {
        if (empty($input)) {
            return "";
        }
        $data = self::request("/mapping/map?value={$input}");
        return arr_get($data, 'hash');
    }

    /**
     * 转换为hash
     * @throws AppException
     */
    public static function toHashBatch(string $inputs): array
    {
        if (empty($inputs)) {
            throw new AppException("参数缺失");
        }
        $items = [];
        $list = explode(',', $inputs);
        foreach ($list as $item) {
            $data = self::request("/mapping/map?value={$item}");
            $hash = arr_get($data, 'hash');
            $items[$item] = $hash;
        }
        return $items;
    }

    /**
     * 获取原始值
     * @param string $hash
     * @return array|mixed|string
     * @throws AppException
     */
    public static function toOrigin(string $hash): mixed
    {
        if (empty($hash)) {
            return "";
        }

        $data = self::request("/mapping/value?hash={$hash}");
        return arr_get($data, 'value');
    }

    /**
     * 获取原始值
     * @param array $hash
     * @return array
     * @throws AppException
     */
    public static function toOriginBatch(array $hash): array
    {
        if (empty($hash)) {
            return [];
        }
        $data = self::request("/mapping/value/batch", [
            'hash' => $hash,
            'system' => 'market'
        ]);
        $ret = [];
        $items = arr_get($data, 'items');
        foreach ($items as $item) {
            $ret[$item['hash']] = $item['value'];
        }
        return $ret;
    }

    /**
     * 获取掩码值
     * @param string $hash
     * @return array|mixed|string
     * @throws AppException
     */
    public static function toMask(string $hash): mixed
    {
        if (empty($hash)) {
            return "";
        }
        $data = self::request("/mapping/mask?hash={$hash}");
        return arr_get($data, 'mask');
    }
}