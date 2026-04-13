<?php

namespace App\Support\Trait;

use WLib\Db\WDb;

/**
 * Date：2023/9/4
 * Description: 表功具
 */
trait TableTrait
{
    /**
     * @var array|int[]
     */
    protected array $typeMap = [
        'week' => 7,
        'hash' => 16,
        'day' => 31,
    ];

    /**
     * 根据hash/week/day分表
     * @param string $table
     * @param string $name
     * @param string $type
     * @return void
     */
    protected function createTable(string $table, string $name, string $type): void
    {
        $this->info("$name:开始任务");

        if (!isset($this->typeMap[$type])) {
            $this->error("分表规则类型不存在");
            return;
        }

        if (!$this->tableExist($table)) {
            $this->error("$name:原始{$table}表不存在");
            return;
        }

        $len = $this->typeMap[$type];
        for ($i = 1; $i <= $len; $i++) {

            if ($type == 'week') {
                $suffix = $i;
            } else {
                $suffix = str_pad($i, 2, '0', STR_PAD_LEFT);
            }

            $newTable = "{$table}_{$suffix}";
            if ($this->tableExist($newTable)) {
                $this->info("$name:{$newTable}表已存在");
                continue;
            }

            $this->info("$name:{$newTable}表开始创建");
            $this->createTableByLike($newTable, $table);
        }

        $this->info("$name:结束任务");
    }

    /**
     * 按月分表
     * @param string $originTable
     * @param string $name
     * @param int    $timestamp
     * @return void
     */
    protected function createTableByMonth(string $originTable, string $name, int $timestamp): void
    {
        $newTable = "{$originTable}_" . date("ym", $timestamp);

        // $this->info("$name:{$newTable}:开始任务");

        if ($this->tableExist($newTable)) {
            $this->info("$name:{$newTable}:表已存在");
            return;
        }

        $this->createTableByLike($newTable, $originTable);
        $this->info("$name:{$newTable}:执行完成");
    }

    /**
     * 按年分表
     * @param string $originTable
     * @param string $name
     * @param int $timestamp
     * @param string|null $targetTable
     * @return void
     */
    protected function createTableByYear(
        string $originTable,
        string $name,
        int $timestamp,
        ?string $targetTable = ''
    ): void {
        if (!empty($targetTable)) {
            $newTable = "{$targetTable}_" . date("y", $timestamp);
        } else {
            $newTable = "{$originTable}_" . date("y", $timestamp);
        }

        //$this->info("$name:{$newTable}:开始任务");

        if ($this->tableExist($newTable)) {
            $this->info("$name:{$newTable}:表已存在");
            return;
        }

        $this->createTableByLike($newTable, $originTable);
        $this->info("$name:{$newTable}:执行完成");
    }

    /**
     * 根本旧表结构创建新表
     * @param $newTable
     * @param $originTable
     * @return void
     */
    protected function createTableByLike($newTable, $originTable): void
    {
        WDb::execute("CREATE TABLE `$newTable` LIKE `{$originTable}`");
    }

    /**
     * 表是否存在
     * @param string $table
     * @return bool
     */
    protected function tableExist(string $table): bool
    {
        $dbName = \Hyperf\Config\config('databases.default.database');
        $sql = "SELECT *  FROM information_schema.TABLES  WHERE TABLE_SCHEMA='$dbName' AND  TABLE_NAME = '$table'";
        $exist = WDb::getVar($sql);
        return (bool)$exist;
    }
}
