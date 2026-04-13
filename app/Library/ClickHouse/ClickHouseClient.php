<?php

namespace App\Library\ClickHouse;

use ClickHouseDB\Client;
use ClickHouseDB\Statement;

/**
 * @link https://github.com/smi2/phpClickHouse/tree/master/example
 * Date：2026/4/10
 * Description:
 *
 */
class ClickHouseClient
{
    private static ?Client $client = null;

    /**
     * 获取单例 Client 实例
     */
    private static function getClient(): Client
    {
        if (self::$client === null) {
            $config = [
                'host' => \Hyperf\Support\env('CLICKHOUSE_HOST', '127.0.0.1'),
                'port' => \Hyperf\Support\env('CLICKHOUSE_PORT', '8123'),
                'username' => \Hyperf\Support\env('CLICKHOUSE_USERNAME', 'default'),
                'password' => \Hyperf\Support\env('CLICKHOUSE_PASSWORD', ''),
                'database' => \Hyperf\Support\env('CLICKHOUSE_DATABASE', 'default'),
                'https' => (bool)\Hyperf\Support\env('CLICKHOUSE_HTTPS', false),
            ];
            self::$client = new Client($config);
            self::$client->database($config['database']);
            self::$client->setTimeout(10);
            //self::$client->settings()->set("ssl_verify_peer", 0);
            self::$client->setConnectTimeOut(5);
        }
        return self::$client;
    }

    public static function ping(): bool
    {
        return self::getClient()->ping();
    }

    public static function query(string $sql, array $bindings = []): array
    {
        $statement = self::getClient()->select($sql, $bindings);
        return $statement->rows();
    }

    public static function write(string $sql, array $bindings = []): Statement
    {
        return self::getClient()->write($sql, $bindings);
    }

    /**
     * @param string $table
     * @param array $values
     * @param array $columns
     * @return Statement
     */
    public static function insert(string $table, array $values, array $columns = []): Statement
    {
        return self::getClient()->insert($table, $values, $columns);
    }
}