<?php

namespace App\Library;


use App\Common\Log\AppLog;
use WLib\Exception\AppException;
use WLib\Lib\HttpClient;
use WLib\WLog;

/**
 * Date：2023/8/7
 * Description: 消息告警
 */
class MessageAlert
{
    /**
     *
     * @param string $title
     * @param string|array $message
     * @param string $group
     * @param $env
     * @param string $country
     * @return void
     */
    public static function alert(string $title, string|array $message, string $group = 'system', $env = null, string $country = ""): void
    {
        if (is_array($message)) {
            AppLog::info($title, $message);
            $message = json_encode($message, 320);
        } else {
            AppLog::info($title, [
                'data' => $message,
            ]);
        }
        try {
            $env = !empty($env) ? $env : \Hyperf\Config\config('app_env', 'dev');
            $country = !empty($country) ? $country : \Hyperf\Config\config('app_country');
            if (empty($country) && $env == 'production') {
                $group = "system";
            }
            self::send("SYSTEM-EXCEPTION-NOTICE", [
                'title' => $title,
                'message' => $message,
                'env' => $env,
                'country' => $country,
                'group' => $group,
                'from' => \Hyperf\Config\config('app_name'),
            ]);
        } catch (\Throwable $throwable) {
            WLog::error("发送告警消息失败:{$title},$message" . $throwable->getMessage());
        }
    }

    /**
     * 普通消息告警
     * @param        $title
     * @param        $message
     * @param string $country
     * @param string $group
     * @param string $env
     * @return void
     */
    public static function notice($title, $message, string $country = 'mx', string $group = '', string $env = ''): void
    {
        try {
            self::send(\Hyperf\Config\config('alert.common_notice'), [
                'title' => $title,
                'message' => $message,
                'country' => strtolower($country),
                'group' => $group,
                'env' => $env,
            ]);
        } catch (\Throwable $throwable) {
            self::log("发送失败:{$title}" . $throwable->getMessage());
        }
    }

    /**
     * @param $title
     * @param $message
     * @return void
     */
    public static function tmpNotice($title, $message): void
    {
        try {
            $group = 'tmp_notice';
            $group = isDebug() ? 'tmp' : $group;
            self::send(\Hyperf\Config\config('alert.common_notice'), [
                'title' => $title,
                'message' => is_array($message) ? json_encode($message, 320) : $message,
                'country' => 'mx',
                'group' => $group,
            ]);
        } catch (\Throwable $throwable) {
            self::log("发送失败:{$title}" . $throwable->getMessage());
        }
    }

    /**
     * @param string $alertId
     * @param array $data
     * @param string $ip
     * @param string $serviceName
     * @param string $trackId
     * @param string $developerName
     * @throws AppException
     */
    public static function send(
        string     $alertId,
        array      $data,
        array|null $alertUserId = null,
        string     $ip = '',
        string     $serviceName = '',
        string     $trackId = '',
        string     $developerName = ''
    ): array
    {
        $json = [
            'trackId' => $trackId,
            'alertId' => $alertId,
            'ip' => $ip,
            'time_ms' => intval(microtime(true) * 1000),
            'serviceName' => $serviceName,
            'developerName' => $developerName,
            'data' => $data,
            'alertUserId' => $alertUserId
        ];
        self::log($json);
        $str = json_encode($json, 320);
        $alertConfig = self::config();
        try {
            $sign = md5($alertConfig['key'] . $str);
            return self::request($sign, $str);
        } catch (\Throwable $e) {
            self::log('告警异常' . $e->getMessage());
            return [
                'status' => false,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * @throws AppException
     */
    protected static function request(string $sign, string $data): mixed
    {
        $config = self::config();
        $url = $config['url'];

        if (!$url) {
            self::log('告警服务未配置服务url');
            return [
                'status' => false,
                'msg' => '告警服务未配置服务url',
            ];
        }

        if (!$config['enable']) {
            self::log("已停用告警配置");
            return [
                'status' => false,
                'msg' => '已停用告警配置',
            ];
        }

        $client = new HttpClient($url);
        $client->setHeaders(['sign' => $sign]);
        $client->setData($data);
        $client->execute();
        $status = $client->getResponseStatus();
        if ($status != 200) {
            self::log(json_encode([
                "msg" => "告警服务响应非200状态",
                'status' => $status,

            ], 320));
            return [
                'status' => false,
                'msg' => '告警服务响应非200状态',
            ];
        }

        $res = $client->getResponseBody();
        $data = json_decode($res, true) ?: [];
        $status = arr_get($data, 'code') === 0;
        if (!$status) {
            self::log(['message' => '告警服务响应失败:', 'response' => $data, 'src' => $res]);
        }
        $data['status'] = $status;
        return $data;
    }

    /**
     * @param $str
     * @return void
     */
    protected static function log($str): void
    {
        if (is_array($str)) {
            WLog::info("告警发送", $str);
        } else {
            WLog::info("告警发送:{$str}");
        }
    }

    /**
     * @return array|mixed
     * @throws AppException
     */
    protected static function config(): mixed
    {
        $config = \Hyperf\Config\config("alert");

        if (empty($config)) {
            throw new AppException("未配置告警配置");
        }

        if (empty($config['url'])) {
            throw new AppException("未配置告警url");
        }

        if (empty($config['key'])) {
            throw new AppException("未配置告警key");
        }

        return $config;
    }
}
