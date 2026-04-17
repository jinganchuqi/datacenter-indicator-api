<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Library\WDate2;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory;
use Psr\Http\Message\MessageInterface;
use WLib\Db\WDb;

/**
 * Date：2026/4/17
 * Description: 订单统计
 *
 */
class MetricOrderController extends AbstractController
{
    /**
     * 当日申请的转化
     * @param ResponseInterface $response
     * @return MessageInterface|\Psr\Http\Message\ResponseInterface
     * @throws \Prometheus\Exception\MetricsRegistrationException
     * @throws \Throwable
     */
    public function dtApply(ResponseInterface $response): MessageInterface|\Psr\Http\Message\ResponseInterface
    {
        $connection = WDb::connection('mysql');
        $countries = $connection->getData("SELECT country,bid FROM conf.`app` GROUP BY country,bid");
        $items = [];

        foreach ($countries as $item) {
            $country = strtolower($item->country);
            if (!WDate2::countryIsValid($country)) {
                continue;
            }
            $dt = WDate2::getInstance($country)->format("Y-m-d");
            $stats = $connection->getData("
SELECT
	bid,
	dt,
	a.app_id,
	COUNT(*) as apply_cnt,
	SUM(IF(b.submited_time is not null,1,0)) as push_cnt,
	SUM(IF(b.reviewed_time is not null and `status`>5,1,0)) as pass_cnt,
	SUM(IF(b.disbursed_time is not null and `status`>8,1,0)) as disburse_cnt,
	SUM(IF(b.paid_off_time is not null,1,0)) as repaid_cnt,
	
	COUNT(DISTINCT a.mobile) as apply_usr_cnt,
	COUNT(DISTINCT IF(b.submited_time is not null,a.mobile,NULL)) as push_usr_cnt,
	COUNT(DISTINCT IF(b.reviewed_time is not null and `status`>5,a.mobile,NULL)) as pass_usr_cnt,
	COUNT(DISTINCT IF(b.disbursed_time is not null and `status`>8,a.mobile,NULL)) as disburse_usr_cnt,
	COUNT(DISTINCT IF(b.paid_off_time is not null and `status`>8,a.mobile,NULL)) as repaid_usr_cnt
FROM
	`application_idx` as a
	LEFT JOIN application as b on a.mobile=b.mobile and a.user_id=b.user_id and a.sn=b.sn
WHERE
	dt = ? and a.bid= ?
GROUP BY bid,dt,a.app_id", [$dt, $item->bid]);
            foreach ($stats as $stat) {
                $stat->country = $item->country;
                $items[] = $stat;
            }
        }


        // 1. 提前定义步骤映射
        $steps = [
            'apply_cnt' => 'apply_cnt',
            'push_cnt' => 'push_cnt',
            'pass_cnt' => 'pass_cnt',
            'disburse_cnt' => 'disburse_cnt',
            'apply_usr_cnt' => 'apply_usr_cnt',
            'push_usr_cnt' => 'push_usr_cnt',
            'pass_usr_cnt' => 'pass_usr_cnt',
            'disburse_usr_cnt' => 'disburse_usr_cnt',
            'repaid_cnt' => 'repaid_cnt',
            'repaid_usr_cnt' => 'repaid_usr_cnt',
        ];

        // 2. 注册指标：标签只定义 [app_id, event]
        $registry = new CollectorRegistry(new InMemory(), false);
        $funnelGauge = $registry->getOrRegisterGauge('order', 'dt_apply_count', '当日申请订单转化', [
            'country',
            'bid',
            'app_id',
            'event'
        ]);

        // 4. 遍历处理
        foreach ($items as $row) {
            $row = (array)$row;
            // 关键改动：labels 只保留 app_id，共 1 个值
            $labels = [
                $row['country'],
                $row['bid'],
                (string)$row['app_id'],
            ];
            // 填充计数指标：merge 后变为 [app_id, event]，共 2 个值，与注册匹配
            foreach (array_keys($steps) as $field) {
                $funnelGauge->set((float)($row[$field] ?? 0), array_merge($labels, [$field]));
            }
        }

        // 5. 渲染输出
        $renderer = new RenderTextFormat();
        return $response->withHeader('Content-Type', RenderTextFormat::MIME_TYPE . '; charset=utf-8')
            ->withBody(new SwooleStream($renderer->render($registry->getMetricFamilySamples())));
    }


    /**
     * 辅助计算转化率
     */
    private function setRate($gauge, $labels, $row, $numField, $denField, $type): void
    {
        $val = ($row[$denField] > 0) ? round($row[$numField] / $row[$denField], 4) : 0;
        $gauge->set((float)$val, array_merge($labels, [$type]));
    }
}
