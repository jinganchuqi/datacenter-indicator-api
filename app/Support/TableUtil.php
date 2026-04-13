<?php

namespace App\Support;

use App\Exception\AppException;
use DateTime;

class TableUtil
{
    /**
     * 按星期分表
     * @param string $table
     * @param int $timestamp 时间戳秒
     * @return string
     */

    public static function weekTable(string $table, int $timestamp): string
    {
        return $table . "_" . date("N", $timestamp);
    }

    public static function ym($timestamp = null): string
    {
        $timestamp = !empty($timestamp) ? $timestamp : time();
        return date('ym', $timestamp);
    }

    public static function y($timestamp = null): string
    {
        $timestamp = !empty($timestamp) ? $timestamp : time();
        return date('y', $timestamp);
    }

    public static function yearAndSeason($timestamp = null): string
    {
        $timestamp = !empty($timestamp) ? $timestamp : time();
        $season = ceil((date('n', $timestamp)) / 3);
        return date('y', $timestamp) . "0{$season}";
    }

    /**
     * @param $table
     * @param $startDate
     * @param $endDate
     * @return array
     * @throws AppException
     */
    public static function getYmTables($table, $startDate, $endDate): array
    {
        $timezone = new \DateTimeZone(\Hyperf\Config\config("app_timezone"));

        $d1 = new DateTime($startDate);
        $d1->setTimezone($timezone);

        $d2 = new DateTime($endDate);
        $d2->setTimezone($timezone);

        $interval = $d2->diff($d1);
        $month = $interval->format('%m');
        if ($month > 2) {
            throw new AppException("查询起始时间不能超过3个月");
        }

        $month = max($month, 1);
        $tables = [];

        $startTimestamp = strtotime($startDate);
        $thisMonthDate = date('Ym');
        for ($i = 0; $i <= $month; $i++) {
            if ($i == 0) {
                $monthTimestamp = $startTimestamp;
            } else {
                $monthTimestamp = strtotime("+{$i} month", $startTimestamp);
            }
            if (date('Ym', $monthTimestamp) > $thisMonthDate) {
                continue;
            }
            $suffix = date('ym', $monthTimestamp);
            $tables[] = "{$table}_{$suffix}";
        }
        $endSuffix = date('ym', strtotime($endDate));
        $endTable = "{$table}_{$endSuffix}";
        if (!in_array($endTable, $tables)) {
            $tables[] = $endTable;
        }
        return $tables;
    }

    /**
     * 获取联表SQL
     * @throws AppException
     */
    public static function getUnionSQL($sqlTemplate, $table, $startTime, $endTime, $union = " union all "): string
    {
        $tables = self::getYmTables($table, date('Y-m-d', $startTime), date('Y-m-d', $endTime));
        $sql = "";
        foreach ($tables as $table) {
            $SQL = $sqlTemplate;
            $sql .= str_replace('__table__', $table, $SQL) . $union;
        }
        return trim($sql, $union);
    }
}