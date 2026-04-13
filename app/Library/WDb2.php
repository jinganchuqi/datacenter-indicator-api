<?php

namespace App\Library;

use WLib\Db\WDb;
use WLib\Db\WDbConnect;
/**
 * Class WDb
 * @package WLib\Db
 * @method static insert(string $table, array $data)
 * @method static insertGetId(string $table, array $data)
 * @method static insertBatch(string $table, array $data)
 * @method static insertInto(string $table, array $data)
 * @method static insertOnReplace(string $table, array $data)
 * @method static insertOnIgnore(string $table, array $data)
 * @method static insertOnDuplicate(string $table, array $data, array $update)
 * @method static delete(string $table, array $data)
 * @method static update(string $table, array $data, array $where)
 * @method static upsert(string $table, array $data, array $where)
 * @method static execute(string $sql, array $bindings = [])
 * @method static getData(string $sql, array $bindings = [])
 * @method static getLine(string $query, array $bindings = [])
 * @method static getVar(string $query, array $bindings = [])
 */
class WDb2 extends WDb
{
    protected static array $instance = [];

    public static function __callStatic($name, $arguments)
    {
        $db = self::connection();
        return $db->{$name}(...$arguments);
    }

    public static function connection(string $poolName = 'default'): WDbConnect
    {
        if (!isset(self::$instance[$poolName])) {
            self::$instance[$poolName] = new WDbConnect2($poolName);
        }
        return self::$instance[$poolName];
    }
}