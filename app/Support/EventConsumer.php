<?php

namespace App\Support;

use Hyperf\Kafka\AbstractConsumer;
use Hyperf\Kafka\Result;
use longlang\phpkafka\Consumer\ConsumeMessage;
use WLib\WLog;

/**
 * @link https://ek8l1y505u.feishu.cn/wiki/Nj6MwvLJdipqggkz6Ivcxm2enqg
 * @link https://ek8l1y505u.feishu.cn/wiki/ZGm2wXAz4iJKrQkh0AycsUJ0nVf
 * Date：2026/4/7
 *
 */
abstract class EventConsumer extends AbstractConsumer
{
    /**
     * @param ConsumeMessage $message
     * @return string|null
     */
    public function consume(ConsumeMessage $message): ?string
    {
        $value = $message->getValue();
        $data = json_decode($value, true);
        if (!$this->validateDataFormat($data)) {
            return Result::ACK;
        }
        try {
            $this->subscribe($data['type'], $data, $message, []);
        } catch (\Throwable $throwable) {
            WLog::error("事件订阅异常:{$throwable->getMessage()}", [
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'code' => $throwable->getCode(),
                'message' => $throwable->getMessage(),
            ]);
        }
        return Result::ACK;
    }

    /**
     * @param                $eventType
     * @param                $data
     * @param ConsumeMessage $message
     * @param                $offsets
     * @return bool
     */
    abstract protected function subscribe($eventType, $data, ConsumeMessage $message, $offsets): bool;


    /**
     * 验证必要参数
     * @param $data
     * @return bool
     */
    protected function validateDataFormat($data): bool
    {
        if (empty($data)) {
            return false;
        }
        $mustFiled = [
            'event_id',
            'type',
        ];
        foreach ($mustFiled as $field) {
            if (!isset($data[$field])) {
                return false;
            }
        }
        return true;
    }
}
