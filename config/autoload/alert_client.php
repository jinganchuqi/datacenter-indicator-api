<?php

declare(strict_types=1);

$appConfig = loadAppConfig();

return [
    "key" => \Hyperf\Support\env("ALERT_CLIENT_KEY",get_value($appConfig,'alert.key')),
    "server_url" => \Hyperf\Support\env("ALERT_SERVER_URL", \Hyperf\Support\env("ALERT_CLIENT_KEY",get_value($appConfig,'alert.server_url'))),
    "log_dir" => \Hyperf\Support\env("ALERT_EVENT_LOG_DIR", "/data/log"),
];
