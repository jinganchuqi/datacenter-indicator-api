<?php

namespace App\Command;

use App\Exception\AppException;
use App\Job\SyncRiskDataJob;
use App\Library\HashTableUtil;
use App\Service\DecidePlatformData\DecidePlatformDataService;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use WLib\Db\WDb;

#[Command]
class DemoCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    #[Inject]
    protected ConfigInterface $config;

    protected array $channelMap = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('demo');
    }

    protected function getArguments(): array
    {
        return [
            ['act', InputArgument::OPTIONAL, '类型'],
        ];
    }

    /**
     * @return void
     * @throws AppException
     */
    public function handle(): void
    {
        $act = $this->input->getArgument('act');
        if (!method_exists($this, $act)) {
            $this->line("方法不存在{$act}");
            return;
        }
        $this->{$act}();
    }

    public function test(): void
    {
        WDb::connection('clickhouse')->insert("test", [
            "app_id" => "625",
            "device_uuid" => "test",
            "dt" => date('Y-m-d'),
            "request_id" => "123",
            "event_name" => "start",
            "event_time" => time(),
            "data" => json_encode(['test' => 1]),
        ]);
        echo 1222;
    }

    public function app()
    {
        $apps = [
            528,
            529,
            533,
            555,
            561,
            562,
            564,
            567,
            568,
            569,
            571,
            572,
            573,
        ];
        foreach ($apps as $app) {
            WDb::execute("INSERT INTO conf.app (`id`, `bid`, `app_group_id`, `country`) values (?,?,?,?)", [$app, 'NG01', $app, 'NG']);
        }
        echo 2333;
        return;
    }
}