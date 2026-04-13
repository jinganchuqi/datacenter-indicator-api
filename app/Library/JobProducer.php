<?php

namespace App\Library;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Context\ApplicationContext;

trait JobProducer
{
    /**
     * @param int $delay
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function push(int $delay = 0): bool
    {
        $delayTime = date('Y-m-d H:i:s', time() + $delay);
        //WLog::info("JobProducer:{$this->name},delay:{$delay},delayTime:{$delayTime}");
        return ApplicationContext::getContainer()
            ->get(DriverFactory::class)
            ->get($this->name)
            ->push($this, $delay);
    }
}