<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use Hyperf\DbConnection\Db;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory;
use Psr\Http\Message\MessageInterface;

/**
 * Date：2026/4/13
 * Description: 作用描述
 *
 */
class MetricsController extends AbstractController
{
    /**
     * app转化
     * @return void
     */
    public function appConv()
    {

    }

    /**
     * 当日发生数据统计
     * @param ResponseInterface $response
     * @return MessageInterface|\Psr\Http\Message\ResponseInterface
     * @throws \Prometheus\Exception\MetricsRegistrationException
     * @throws \Throwable
     */
    public function appDone(ResponseInterface $response): MessageInterface|\Psr\Http\Message\ResponseInterface
    {
        // 1. 提前定义步骤映射
        $steps = [
            'first_open' => 'first_open',
            'otp_started' => 'otp_validation_started',
            'otp_passed' => 'otp_validation_passed',
            'liveness_started' => 'liveness_validation_started',
            'liveness_passed' => 'liveness_validation_passed',
            'ocr_started' => 'id_scan_started',
            'ocr_passed' => 'id_scan_completed',
            'register' => 'register',
        ];

        // 2. 注册指标：标签只定义 [app_id, event]
        $registry = new CollectorRegistry(new InMemory(), false);
        $funnelGauge = $registry->getOrRegisterGauge('app', 'done_count', 'app事件发生计数', ['app_id', 'event']);
        $rateGauge = $registry->getOrRegisterGauge('app', 'done_rate', 'app事件发生率', ['app_id', 'event']);

        // 3. 执行 ClickHouse SQL (保持逻辑不变，获取每个app_id最新的数据)
        $sql = "
    SELECT
        main.app_id,        
        main.dt AS last_dt,
        uniqIf(device_uuid, event_name = 'first_open') AS first_open,
        uniqIf(device_uuid, event_name = 'otp_validation_started') AS otp_started,
        uniqIf(device_uuid, event_name = 'otp_validation_passed') AS otp_passed,
        uniqIf(device_uuid, event_name = 'liveness_validation_started') AS liveness_started,
        uniqIf(device_uuid, event_name = 'liveness_validation_passed') AS liveness_passed,
        uniqIf(device_uuid, event_name = 'id_scan_started') AS ocr_started,
        uniqIf(device_uuid, event_name = 'id_scan_completed') AS ocr_passed,
        uniqIf(device_uuid, event_name = 'mobile_auth_passed' AND fs['auth_type'] = 'signup') AS register
    FROM log.app_event AS main
    INNER JOIN (
        SELECT app_id, MAX(dt) AS max_dt
        FROM log.app_event
        WHERE length(dt) = 10
        GROUP BY app_id
    ) AS latest ON main.app_id = latest.app_id AND main.dt = latest.max_dt
    WHERE 
        event_name IN (
            'first_open', 'otp_validation_started', 'otp_validation_passed', 
            'liveness_validation_started', 'liveness_validation_passed', 
            'id_scan_started', 'id_scan_completed', 'mobile_auth_passed'
        )
    GROUP BY main.app_id, main.dt
    ORDER BY main.app_id ASC
";

        $data = Db::select($sql);

        // 4. 遍历处理
        foreach ($data as $row) {
            $row = (array)$row;

            // 关键改动：labels 只保留 app_id，共 1 个值
            $labels = [(string)$row['app_id']];

            // 填充计数指标：merge 后变为 [app_id, event]，共 2 个值，与注册匹配
            foreach (array_keys($steps) as $field) {
                $funnelGauge->set((float)($row[$field] ?? 0), array_merge($labels, [$field]));
            }

            // 填充转化率指标：确保 setRate 内部处理也是 1+1=2 个标签
            $this->setRate($rateGauge, $labels, $row, 'register', 'first_open', 'total');
            $this->setRate($rateGauge, $labels, $row, 'otp_passed', 'otp_started', 'otp');
            $this->setRate($rateGauge, $labels, $row, 'liveness_passed', 'liveness_started', 'liveness');
            $this->setRate($rateGauge, $labels, $row, 'ocr_passed', 'ocr_started', 'ocr');
        }

        // 5. 渲染输出
        $renderer = new RenderTextFormat();
        return $response->withHeader('Content-Type', RenderTextFormat::MIME_TYPE . '; charset=utf-8')
            ->withBody(new SwooleStream($renderer->render($registry->getMetricFamilySamples())));
    }

    /**
     * 7日内发生数据统计
     * @param ResponseInterface $response
     * @return MessageInterface|\Psr\Http\Message\ResponseInterface
     * @throws \Prometheus\Exception\MetricsRegistrationException
     * @throws \Throwable
     */
    public function appDay7(ResponseInterface $response): MessageInterface|\Psr\Http\Message\ResponseInterface
    {
        // 1. 提前定义步骤映射
        $steps = [
            'first_open' => 'first_open',
            'otp_started' => 'otp_validation_started',
            'otp_passed' => 'otp_validation_passed',
            'liveness_started' => 'liveness_validation_started',
            'liveness_passed' => 'liveness_validation_passed',
            'ocr_started' => 'id_scan_started',
            'ocr_passed' => 'id_scan_completed',
            'register' => 'register',
        ];

        // 2. 注册指标：标签只定义 [app_id, event]
        $registry = new CollectorRegistry(new InMemory(), false);
        $funnelGauge = $registry->getOrRegisterGauge('app', 'day7_count', 'app7日内事件发生计数', ['app_id', 'event']);

        // 3. 执行 ClickHouse SQL (保持逻辑不变，获取每个app_id最新的数据)
        $sql = "
          SELECT
            main.app_id,        
            main.dt AS last_dt,
            uniqIf(device_uuid, event_name = 'first_open') AS first_open,
            uniqIf(device_uuid, event_name = 'otp_validation_started') AS otp_started,
            uniqIf(device_uuid, event_name = 'otp_validation_passed') AS otp_passed,
            uniqIf(device_uuid, event_name = 'liveness_validation_started') AS liveness_started,
            uniqIf(device_uuid, event_name = 'liveness_validation_passed') AS liveness_passed,
            uniqIf(device_uuid, event_name = 'id_scan_started') AS ocr_started,
            uniqIf(device_uuid, event_name = 'id_scan_completed') AS ocr_passed,
            uniqIf(device_uuid, event_name = 'mobile_auth_passed' AND fs['auth_type'] = 'signup') AS register
        FROM log.app_event AS main
        WHERE 
            event_name IN (
                'first_open', 'otp_validation_started', 'otp_validation_passed', 
                'liveness_validation_started', 'liveness_validation_passed', 
                'id_scan_started', 'id_scan_completed', 'mobile_auth_passed'
            )
            AND toDateOrNull(dt) >= subtractDays(today(), 7)  -- 扫描最近7天
            AND length(dt) = 10
        GROUP BY main.app_id, main.dt
        ORDER BY main.app_id ASC;
";
        $data = Db::select($sql);

        // 4. 遍历处理
        foreach ($data as $row) {
            $row = (array)$row;

            // 关键改动：labels 只保留 app_id，共 1 个值
            $labels = [(string)$row['app_id']];

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
