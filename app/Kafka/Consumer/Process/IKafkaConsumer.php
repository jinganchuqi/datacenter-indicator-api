<?php

namespace App\Kafka\Consumer\Process;

abstract class IKafkaConsumer
{
    protected string|null $appId;

    protected string|null $appVersion = null;

    /**
     * @var array
     */
    protected array $subscribeEvents = [];

    /**
     * @param string $eventType
     * @param array $data
     */
    public function __construct(protected string $eventType, protected array $data)
    {
        $this->appId = (string)arr_get($this->data, 'appId');
    }

    /**
     * @return bool
     */
    public function isSubscribeEvent(): bool
    {
        if (empty($this->subscribeEvents)) {
            return true;
        }
        if (in_array($this->eventType, $this->subscribeEvents)) {
            return true;
        }
        return false;
    }

    abstract public function handle();
}
