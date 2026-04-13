<?php

namespace App\Util;


use App\Exception\AppException;
use WLib\Lib\HttpClient;
use WLib\WLog;

/**
 * Date：2023/8/7
 * Description: 消息告警
 */
class GolangMessageAlert
{
    public static string $appId = 'cli_9f8434beb8ec900b';

    /**
     * @var string
     */
    public static string $appSecret = 'cDcn6NTONAeJKWaqlKbCRfspQRJUzhFs';

    protected static string $debugGroup = "oc_e22f02802536abb35eeada90687da349";

    /**
     * @var array|string[]
     */
    protected static array $groups = [
        'debug' => 'oc_e22f02802536abb35eeada90687da349',
        'review' => 'oc_b5991c48e5b80d2ee68a1f1a03d07d33',
        'risk_review' => 'oc_4842c99f81bf33b7f090234bfc83410c',
        'am_mx' => 'oc_d13189a0579974bb6118269293598876',
        'metric' => 'oc_d13189a0579974bb6118269293598876',
        'system' => 'oc_5e696a64da3bdc24a2779c25b0b1ea81',
        'risk_decide' => 'oc_5c478f5ad2eea265760e492f38a8fc3d',
    ];

    /**
     * 人工审核通知
     * @param        $message
     * @param bool $debug
     * @return string|null
     */
    public static function manualReviewStartNotice($message, bool $debug = false): ?string
    {
        try {
            $group = $debug ? self::$debugGroup : self::config('risk_review')['group'];
            return self::send(
                $group,
                "风控人审通知-待审核",
                $message,
                'warn'
            );
        } catch (\Throwable $throwable) {
            self::log("发送失败:风控人审通知:{$message}" . $throwable->getMessage());
        }
        return "";
    }

    /**
     * ocr证件人工审核
     * @param $msgId
     * @param        $message
     * @param bool $debug
     * @return void
     */
    public static function ocrReviewEndNotice($msgId, $message, bool $debug = true): void
    {
        try {
            $group = $debug ? self::$debugGroup : self::config('review')['group'];
            self::send(
                $group,
                "证件照审核通知,已审核",
                $message,
                'info',
                $msgId
            );
        } catch (\Throwable $throwable) {
            self::log("发送失败:ocr证件人工审核通知:{$message}" . $throwable->getMessage());
        }
    }


    /**
     * @param $group
     * @param $title
     * @param $message
     * @param string $level
     * @return string|null
     */
    public static function alert($group, $title, $message, string $level = 'warn'): ?string
    {
        try {
            $groupId = self::config($group)['group'];
            return self::send(
                $groupId,
                $title,
                $message,
                $level
            );
        } catch (\Throwable $throwable) {
            self::log("发送告警消息:发送失败:{$message}" . $throwable->getMessage());
        }
        return null;
    }


    /**
     * @param $groupId
     * @param        $title
     * @param        $message
     * @param        $type
     * @param string $msgId
     * @param bool $hideExt
     * @return array|null
     */
    protected static function send($groupId, $title, $message, $type, string $msgId = '', bool $hideExt = true): ?string
    {
        $typeMap = [
            'warn' => 'w',
            'info' => 'r'
        ];
        $wr = $typeMap[$type] ?? 'w';
        $json = [
            'title' => $title,
            'summary' => $message,
            'wr' => $wr,
            'hideExt' => intval($hideExt),
            'msgId' => $msgId,
            'receive' => $groupId
        ];
        self::log($json);
        $jsonStr = json_encode($json, 320);
        try {
            return self::request($jsonStr);
        } catch (\Throwable $e) {
            self::log('告警异常' . $e->getMessage());
        }
        return null;
    }

    /**
     * @throws AppException
     * @throws \App\Exception\AppException
     */
    protected static function request(string $data): ?string
    {
        $config = self::config();
        $url = $config['url'];

        if (!$url) {
            self::log('告警服务未配置服务url');
            return null;
        }

        if (!$config['enable']) {
            self::log("已停用告警配置");
            return null;
        }

        $client = new HttpClient($url);
        $client->setData($data);
        $client->execute();
        $status = $client->getResponseStatus();
        if ($status != 200) {
            self::log(json_encode([
                "msg" => "告警服务响应非200状态",
                'status' => $status,
            ], 320));
            return null;
        }

        $res = $client->getResponseBody();
        $data = json_decode($res, true) ?: [];
        $msgIdSet = arr_get($data, 'msg_id');
        if (empty($msgIdSet)) {
            return null;
        }

        return $msgIdSet[0];
    }

    /**
     * @param $str
     * @return void
     */
    protected static function log($str): void
    {
        if (is_array($str)) {
            WLog::error("告警发送:" . json_encode($str, 320));
        } else {
            WLog::error("告警发送:{$str}");
        }
    }

    /**
     * @param string $groupName
     * @return mixed
     * @throws AppException
     */
    protected static function config(string $groupName = ''): mixed
    {
        $config = \Hyperf\Config\config('alert');
        if (empty($config)) {
            $config = [
                'key' => 'd5u0c0c5a2a5d0j5g0n0a4i1j2d3k0b3',
            ];
        }
        $config['url'] = 'http://feishu.message.api.com:8081/feishu/message';
        if (!empty($groupName)) {
            $config['group'] = self::getGroupId($groupName);
        }
        if (empty($config['key'])) {
            throw new AppException("未配置告警key");
        }
        return $config;
    }


    /**
     * @param $groupName
     * @return string
     * @throws \App\Exception\AppException
     */
    public static function getGroupId($groupName): string
    {
        if (isset(self::$groups[$groupName])) {
            return self::$groups[$groupName];
        }
        return self::$groups['system'];
    }

    /**
     * @throws \App\Exception\AppException
     */
    public static function getFeishuUser(array $names): array
    {
        if (PHP_SAPI == 'cli') {
            $json = file_get_contents(\dirname(__FILE__, 2) . '/Resource/feishu_user.json');
        } else {
            $json = file_get_contents(\dirname(__FILE__, 3) . '/Resource/feishu_user.json');
        }
        if (empty($json)) {
            throw new \App\Exception\AppException("配置文件不存在");
        }
        $items = json_decode($json, true);
        $userIds = [];
        foreach ($items as $item) {
            if (in_array($item['label'], $names)) {
                $userIds[] = $item['user_id'];
            }
        }
        return array_unique($userIds);
    }

}
