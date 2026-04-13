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
     * @param ResponseInterface $response
     * @return MessageInterface|\Psr\Http\Message\ResponseInterface
     * @throws \Prometheus\Exception\MetricsRegistrationException
     * @throws \Throwable
     */
    public function appConv(ResponseInterface $response): MessageInterface|\Psr\Http\Message\ResponseInterface
    {
        // 1. 使用内存存储（因为是实时查询，不需要持久化到 Redis）
        $registry = new CollectorRegistry(new InMemory());

        // 2. 注册指标名称和标签
        $funnelGauge = $registry->getOrRegisterGauge(
            'app',
            'conversion_funnel_count',
            'App conversion funnel steps count',
            ['app_id', 'date', 'step']
        );

        $rateGauge = $registry->getOrRegisterGauge(
            'app',
            'conversion_rate',
            'App conversion funnel rates',
            ['app_id', 'date', 'type']
        );

        // 3. 执行 ClickHouse SQL (直接引用你提供的逻辑)
        // 注意：生产环境建议将 dt 范围改为动态（如最近7天），避免扫描全表
        $sql = "
            SELECT
                app_id,
                first_open_dt,
                uniqIf(device_uuid, event_name = 'first_open') AS first_open,
                uniqIf(device_uuid, event_name = 'otp_validation_started') AS otp_started,
                uniqIf(device_uuid, event_name = 'otp_validation_passed') AS otp_passed,
                uniqIf(device_uuid, event_name = 'liveness_validation_started') AS liveness_started,
                uniqIf(device_uuid, event_name = 'liveness_validation_passed') AS liveness_passed,
                uniqIf(device_uuid, event_name = 'id_scan_started') AS ocr_started,
                uniqIf(device_uuid, event_name = 'id_scan_completed') AS ocr_passed,
                uniqIf(device_uuid, event_name = 'mobile_auth_passed' AND fs['auth_type'] = 'signup') AS register
            FROM (
                SELECT app_id, device_uuid, event_name, fs,
                minIf(dt, event_name = 'first_open') OVER (PARTITION BY device_uuid) AS first_open_dt
                FROM log.app_event
                WHERE dt >= '2026-04-12'
            )
            WHERE first_open_dt IS NOT NULL
            GROUP BY app_id, first_open_dt
            ORDER BY first_open_dt ASC
        ";

        $data = Db::select($sql);

        // 4. 遍历结果并填充到 Prometheus 指标中
        foreach ($data as $row) {
            $labels = [(string)$row['app_id'], (string)$row['first_open_dt']];

            // 填充原始计数 (Count)
            $funnelGauge->set((float)$row['first_open'], array_merge($labels, ['first_open']));
            $funnelGauge->set((float)$row['otp_started'], array_merge($labels, ['otp_started']));
            $funnelGauge->set((float)$row['otp_passed'], array_merge($labels, ['otp_passed']));
            $funnelGauge->set((float)$row['liveness_started'], array_merge($labels, ['liveness_started']));
            $funnelGauge->set((float)$row['liveness_passed'], array_merge($labels, ['liveness_passed']));
            $funnelGauge->set((float)$row['ocr_started'], array_merge($labels, ['ocr_started']));
            $funnelGauge->set((float)$row['ocr_passed'], array_merge($labels, ['ocr_passed']));
            $funnelGauge->set((float)$row['register'], array_merge($labels, ['register']));

            // 填充转化率 (Rate) - 逻辑直接在 PHP 中计算
            if ($row['first_open'] > 0) {
                $rateGauge->set(round($row['register'] / $row['first_open'], 4), array_merge($labels, ['register']));
            }
            if ($row['otp_started'] > 0) {
                $rateGauge->set(round($row['otp_passed'] / $row['otp_started'], 4), array_merge($labels, ['otp']));
            }
            if ($row['liveness_started'] > 0) {
                $rateGauge->set(round($row['liveness_passed'] / $row['liveness_started'], 4), array_merge($labels, ['liveness']));
            }
            if ($row['ocr_started'] > 0) {
                $rateGauge->set(round($row['ocr_passed'] / $row['ocr_started'], 4), array_merge($labels, ['ocr']));
            }
        }

        // 5. 渲染输出
        $renderer = new RenderTextFormat();
        $output = $renderer->render($registry->getMetricFamilySamples());

        return $response->withHeader('Content-Type', RenderTextFormat::MIME_TYPE)
            ->withBody(new SwooleStream($output));
    }
}
