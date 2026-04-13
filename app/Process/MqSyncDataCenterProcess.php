<?php

namespace App\Process;

use Hyperf\AsyncQueue\Process\ConsumerProcess;
use Hyperf\Process\Annotation\Process;

//#[Process(name: "mq_sync_datacenter")]
class MqSyncDataCenterProcess extends ConsumerProcess
{
    protected string $queue = 'mq_sync_datacenter';
}