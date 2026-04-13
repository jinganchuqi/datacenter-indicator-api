<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Log\LogLevel;

$appConfig = loadAppConfig();

return [
    'app_name' => \Hyperf\Support\env('APP_NAME', 'gateway-api-hyperf'),
    'app_env' => \Hyperf\Support\env('APP_ENV', 'production'),
    'app_country' => \Hyperf\Support\env('APP_COUNTRY', 'mx'),
    'app_timezone' => \Hyperf\Support\env('APP_TIMEZONE', get_value($appConfig, 'timezone')),
    'enable_module' => \Hyperf\Support\env('ENABLE_MODULE'),
    'scan_cacheable' => \Hyperf\Support\env('SCAN_CACHEABLE', false),
    'log_sql' => (bool)\Hyperf\Support\env('LOG_SQL', false), // 是否输出执行SQL日志
    'sql_time' => (int)\Hyperf\Support\env('TIME_SQL', 2000), // 输出SQL执行时间大于?毫秒的SQL
    'app_key' => \Hyperf\Support\env('APP_KEY', 'zNg9xndjQpybhavLlkEABC4JRXemrIWV'),//app_key
    'log' => [
        'dir' => \Hyperf\Support\env('LOG_DIR', '/data/log'),
    ],
    'event_center' => [
        'key' => \Hyperf\Support\env('EVENT_CENTER_KEY', ''),//事件中心密钥
        'server' => \Hyperf\Support\env('EVENT_CENTER_SERVER', 'udp://market.api.com:9812'),//事件中心服务端地址
    ],
    'ad_center' => [
        'server' => \Hyperf\Support\env('AD_CENTER_SERVER', 'http://market.api.com:9520'),//广告中心服务端地址
    ],
    'push_center' => [
        'server' => \Hyperf\Support\env('PUSH_CENTER_SERVER', 'http://market.api.com:9520'),//广告中心服务端地址
    ],
    'identity_center' => [
        'server' => \Hyperf\Support\env('IDENTITY_SERVER', 'http://identity.api.com:9522'),//数据加解密中心
    ],
    'risk' => [
        'credit_report_server' => \Hyperf\Support\env('CREDIT_REPORT_SERVER', 'http://risk.api.com:9523'),
        //信用报告
        'est_credit_report_server' => \Hyperf\Support\env('EST_CREDIT_REPORT_SERVER', 'http://market.api.com:9815'),
        //信用报告
        'front_server' => \Hyperf\Support\env('FRONT_SERVER', 'http://8.222.233.232:9090'),
        //风控前筛分值
    ],
    'openapi' => [
        'host' => \Hyperf\Support\env('OPENAPI_HOST', 'http://127.0.0.1:9511'),
        'key' => \Hyperf\Support\env('OPENAPI_HOST', '4ey5H8jfwmBpviEP')
    ],
    'gateway' => [
        'host' => \Hyperf\Support\env('GATEWAY_HOST', 'http://127.0.0.1:9512'),
        'key' => \Hyperf\Support\env('GATEWAY_KEY', 'g9xndLlkEArIWVjQRXempybhavBC4JzN')
    ],
    'alert' => [
        'key' => \Hyperf\Support\env('ALERT_CLIENT_KEY', get_value($appConfig, 'alert.client_key')),
        'url' => \Hyperf\Support\env('ALERT_SERVER_URL', get_value($appConfig, 'alert.server_url')),
        'exception_notice' => \Hyperf\Support\env('ALERT_EXCEPTION_NOTICE'),
        'common_notice' => \Hyperf\Support\env('ALERT_COMMON_NOTICE'),
        'env' => \Hyperf\Support\env('ALERT_ENV'),
        'enable' => (bool)\Hyperf\Support\env('ALERT_ENABLE', true),
    ],
    'golang_alert' => [
        'key' => \Hyperf\Support\env('GOLANG_ALERT_CLIENT_KEY', get_value($appConfig, 'golang_alert.client_key')),
        'url' => \Hyperf\Support\env('GOLANG_ALERT_SERVER_URL', get_value($appConfig, 'golang_alert.server_url')),
        'enable' => (bool)\Hyperf\Support\env('GOLANG_ALERT_ENABLE', true),
        'live_review_group' => \Hyperf\Support\env('GOLANG_ALERT_LIVE_REVIEW_GROUP',
            'oc_e22f02802536abb35eeada90687da349'),
    ],
    'decide_platform' => [
        'merchant_code' => \Hyperf\Support\env('DECIDE_PLATFORM_MERCHANT_CODE'),
        'merchant_key' => \Hyperf\Support\env('DECIDE_PLATFORM_MERCHANT_KEY'),
        'host' => \Hyperf\Support\env('DECIDE_PLATFORM_HOST', 'http://172.21.67.59:7105'),
    ],
    'datacenter' => [
        'bid' => \Hyperf\Support\env('DATACENTER_BID'),
        'access_token' => \Hyperf\Support\env('DATACENTER_ACCESS_TOKEN'),
        'host' => \Hyperf\Support\env('DATACENTER_PLATFORM_HOST', 'http://172.21.67.59:7225'),
    ],
    StdoutLoggerInterface::class => [
        'log_level' => [
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            //LogLevel::DEBUG,
            LogLevel::EMERGENCY,
            LogLevel::ERROR,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
        ],
    ],
    "aliyun" => [
        'oss' => [
            // 授权订单信息
            'endpoint' => \Hyperf\Support\env('ALIYUN_OSS_ENDPOINT', get_value($appConfig, 'aliyun.oss.endpoint')),
            'accessId' => \Hyperf\Support\env('ALIYUN_OSS_ACCESS_ID', get_value($appConfig, 'aliyun.oss.accessId')),
            'secretKey' => \Hyperf\Support\env('ALIYUN_OSS_SECRET_KEY', get_value($appConfig, 'aliyun.oss.secretKey')),
            'bucket' => \Hyperf\Support\env('ALIYUN_OSS_BUCKET', get_value($appConfig, 'aliyun.oss.bucket')),
        ],
        'public' => [
            // 公共oss
            'endpoint' => \Hyperf\Support\env('ALIYUN_OSS_PUBLIC_ENDPOINT',
                get_value($appConfig, 'aliyun.ossPublic.endpoint')),
            'accessId' => \Hyperf\Support\env('ALIYUN_OSS_PUBLIC_ACCESS_ID',
                get_value($appConfig, 'aliyun.ossPublic.accessId')),
            'secretKey' => \Hyperf\Support\env('ALIYUN_OSS_PUBLIC_SECRET_KEY',
                get_value($appConfig, 'aliyun.ossPublic.secretKey')),
            'bucket' => \Hyperf\Support\env('ALIYUN_OSS_PUBLIC_BUCKET',
                get_value($appConfig, 'aliyun.ossPublic.bucket')),
        ],
        'log' => [
            // 日志信息
            'endpoint' => \Hyperf\Support\env('ALIYUN_LOG_ENDPOINT', get_value($appConfig, 'aliyun.log.endpoint')),
            'accessId' => \Hyperf\Support\env('ALIYUN_LOG_ACCESS_ID', get_value($appConfig, 'aliyun.log.accessId')),
            'secretKey' => \Hyperf\Support\env('ALIYUN_LOG_SECRET_KEY', get_value($appConfig, 'aliyun.log.secretKey')),
            'bucket' => \Hyperf\Support\env('ALIYUN_LOG_BUCKET', get_value($appConfig, 'aliyun.log.bucket')),
        ],
        // 客户端日志
        'event_log' => [
            'endpoint' => \Hyperf\Support\env('ALIYUN_EVENT_LOG_ENDPOINT',
                get_value($appConfig, 'aliyun.log.endpoint')),
            'accessId' => \Hyperf\Support\env('ALIYUN_EVENT_LOG_ACCESS_ID',
                get_value($appConfig, 'aliyun.log.accessId')),
            'secretKey' => \Hyperf\Support\env('ALIYUN_EVENT_LOG_SECRET_KEY',
                get_value($appConfig, 'aliyun.log.secretKey')),
            'project' => \Hyperf\Support\env('ALIYUN_EVENT_LOG_PROJECT',
                get_value($appConfig, 'aliyun.log.project')),
            'store' => \Hyperf\Support\env('ALIYUN_EVENT_LOG_STORE',
                get_value($appConfig, 'aliyun.log.store'))
        ],
    ],
];
