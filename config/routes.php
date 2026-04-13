<?php
declare(strict_types=1);

use Hyperf\HttpServer\Router\Router;

Router::get('/api/ping', [App\Controller\Api\ApiController::class, 'ping']);//心跳
Router::post('/api/alert', [App\Controller\Api\AlertController::class, 'alert']);//告警
Router::get('/api/metrics/app-conv', [App\Controller\Api\MetricsController::class, 'appConv']);//app当日转化事件统计
Router::get('/api/metrics/app-done', [App\Controller\Api\MetricsController::class, 'appDone']);//app当日发生的事件统计
Router::get('/api/metrics/app-day7', [App\Controller\Api\MetricsController::class, 'appDay7']);//app当日转化事件统计