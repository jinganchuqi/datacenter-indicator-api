<?php

declare(strict_types=1);

namespace App\Listener;

use App\Common\Log\AppLog;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Collection\Arr;
use function Hyperf\Config\config;

#[Listener]
class DbQueryExecutedListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            QueryExecuted::class,
        ];
    }

    /**
     * @param object $event
     * @return void
     */
    public function process(object $event): void
    {
        if ($event instanceof QueryExecuted &&
            config('log_sql') &&
            $event->time > config('sql_time')) {
            $sql = $event->sql;
            if (!Arr::isAssoc($event->bindings)) {
                $position = 0;
                foreach ($event->bindings as $value) {
                    if (($position = strpos($sql, '?', $position)) === false) {
                        break;
                    }
                    $value = "'$value'";
                    $sql = substr_replace($sql, $value, $position, 1);
                    $position += strlen($value);
                }
            }
            AppLog::info("Execute SQL", ['sql' => $sql, 'time' => "{$event->time}ms"], 'SQL');
        }
    }
}
