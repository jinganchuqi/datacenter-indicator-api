<?php

declare(strict_types=1);

use Hyperf\Collection\Collection;

if (!function_exists('get_value')) {
    /**
     * 判断数组或对象中是否存在某个属性
     * @param      $item
     * @param      $key
     * @param null $default
     * @return mixed|string|array|int|null
     */
    function get_value($item, $key, $default = null): mixed
    {
        if (empty($item)) {
            return $default;
        }

        if (is_object($item)) {

            if (property_exists($item, $key)) {
                $value = $item->$key;
                return !is_null($value) ? $value : $default;
            }

            if ($item instanceof Hyperf\Database\Model\Model) {
                $keys = explode('.', $key);
                if (count($keys) == 2) {
                    $firstKey = $keys[0];
                    $secondKey = $keys[1];
                    return get_value($item->$firstKey, $secondKey, $default);
                }
                return $item->$key === null || $item->$key === '' ? $default : $item->$key;
            }
        }

        if (is_array($item)) {

            $keys = explode('.', $key);
            if (count($keys) == 1) {
                return $item[$key] ?? $default;
            }

            return getVal($item, $keys, $default);
        }

        return $default;
    }
}


function isDebug(): bool
{
    return \Hyperf\Config\config('app_env') == 'dev';
}

/**
 * @param array $data
 * @param array $keys
 * @param       $default
 * @return mixed
 */
function getVal(array $data, array $keys, $default): mixed
{
    $len = count($keys);
    for ($i = 0; $i < $len; $i++) {

        if (!isset($data[$keys[$i]])) {
            return $default;
        }

        if ($i == $len - 1) {
            return $data[$keys[$i]];
        }

        $data = $data[$keys[$i]];
    }
    return $default;
}

/**
 * 下划线转驼峰
 * @param string $words
 * @param string $separator
 * @return string
 */
function toCamelCase(string $words, string $separator = '_'): string
{
    if (empty($words)) {
        return "";
    }

    $words = $separator . str_replace($separator, " ", strtolower($words));
    return ltrim(str_replace(" ", "", ucwords($words)), $separator);
}

/**
 * 数据Key值下划线转驼峰
 * @param array|Collection|null $data
 * @return array|null
 */
function toCamelCaseData(array|Collection|null $data): array|null
{
    if (empty($data)) {
        return $data;
    }

    $ret = [];
    foreach ($data as $key => $val) {

        if (is_string($key)) {
            $camelKey = toCamelCase($key);
        } else {
            $camelKey = $key;
        }

        if (is_array($val)) {
            $val = toCamelCaseData($val);
        } elseif ($val instanceof Collection) {
            $val = toCamelCaseData($val);
        }

        $ret[$camelKey] = $val;
    }

    return $ret;
}


function getMicroTime(): int
{
    $time = microtime(true) * 1000;
    return substr("{$time}", 0, 13) * 1;
}


/**
 * @param        $maskStr
 * @param int $start
 * @param int $length
 * @param string $maskCode
 * @return string|string[]|null
 */
function toMask($maskStr, int $start = 3, int $length = 4, string $maskCode = '*'): array|string|null
{
    if (empty($maskStr)) {
        return '';
    }

    mb_internal_encoding('UTF-8');
    $replaceStr = mb_substr($maskStr, $start, $length, 'utf-8');
    $replaceStrLen = mb_strlen($replaceStr, 'utf-8');
    $maskCodeStr = '';

    if ($replaceStrLen > 0) {
        $maskStrLen = mb_strlen($maskStr, 'utf-8');
        $startStr = mb_substr($maskStr, 0, $start, 'utf-8');
        $endStr = mb_substr($maskStr, $start + $length, $maskStrLen, 'utf-8');
        $maskCodeStr .= str_repeat($maskCode, $replaceStrLen);
        return $startStr . $maskCodeStr . $endStr;
    }

    return preg_replace("/{$replaceStr}/si", $maskCodeStr, $maskStr, 1);
}

/**
 * 是否为手机号
 * @param $mobile
 * @return bool
 */
function isMobile($mobile): bool
{
    if (empty($mobile)) {
        return $mobile;
    }
    $mobile = (string)$mobile;
    $regexp = "/^[123456789]\d{9}$/i";
    preg_match($regexp, $mobile, $matches);
    if (empty($matches)) {
        return false;
    }

    return true;
}


function loadAppConfig()
{
    return [];
    //return include \Hyperf\Support\env('APP_CONF', "/server/conf/app.conf.php");
}

/**
 * @param string $input
 * @return false|string
 */
function gzEncodeData(string $input): bool|string
{
    return gzencode($input, 9);
}

/**
 * @param string $input
 * @return false|string
 */
function gzDecodeData(string $input): bool|string
{
    return gzdecode($input);
}


/**
 */
function loadResource($filename): string
{
    $filename = BASE_PATH . "/storage/resource/$filename";
    if (!file_exists($filename)) {
        return "";
    }

    $content = file_get_contents($filename);
    if (!empty($content)) {
        return $content;
    }

    return "";
}


function tryCatchLog(Throwable $throwable, string $message, array $data = []): void
{
    \WLib\WLog::error($message, [
        'error' => [
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'code' => $throwable->getCode(),
            'message' => $throwable->getMessage(),
        ],
        'data' => $data
    ]);
}


/**
 * @param int|string $code
 * @param string $message
 * @return string
 */
function formatErrorMessage(int|string $code, string $message = ""): string
{
    if (is_string($code)) {
        return $code;
    }

    return $message;
}

function isPWA($appId): bool
{
    return in_array($appId, [699, 698, 697, 696, 695]);
}

function isPwaSystem($appSystem, $orderSystem): bool
{
    if (in_array($appSystem, [5, 6, 7])) {
        return true;
    }
    return $orderSystem === 'pwa';
}


function arrGet(mixed $array, int|string|null $key = null, $type = null, $default = null, $filterEmptyString = false)
{
    $data = arr_get($array, $key);
    if ($data === null) {
        return $default;
    }

    if ($type === null) {
        return $data;
    }

    if ($type == 'int' && $filterEmptyString && $data == '') {
        return $default;
    }

    if ($type == 'string' && $filterEmptyString && $data == '') {
        return $default;
    }

    if ($type == 'float' && $filterEmptyString && $data == '') {
        return $default;
    }

    if ($type == 'int') {
        return (int)$data;
    }

    if ($type == 'string') {
        return (string)$data;
    }

    if ($type == 'float') {
        return (float)$data;
    }

    return $data;
}

function formatTimeToSeconds(int|string|null $time): ?int
{
    if (empty($time)) {
        return $time;
    }
    if (strlen((string)$time) === 13) {
        return intval($time / 1000);
    }
    return $time;
}

/**
 * 格式化毫秒
 * @param $time
 * @return int
 */
function formatMicroTime($time): int
{
    if (empty($time)) {
        return 0;
    }

    if ($time == '*') {
        return 0;
    }

    $time = substr((string)$time, 0, 13);
    return (int)str_pad("{$time}", 13, "0", STR_PAD_RIGHT) * 1;
}