<?php

namespace App\Service\Tools;

use WLib\Db\WDb;
use WLib\Lib\Id2Generator;

class ApplicationTools
{
    protected object $application;

    public function __construct(protected string $sn)
    {
    }

    /**
     * @param $status
     * @return int
     */
    public function getApplyStatus($status = null): int
    {
        if ($status == null) {
            $timestamp = Id2Generator::parse($this->sn)['timestamp'];
            $suffix = date('ym', $timestamp);
            $status = WDb::getVar("select status from loan_market.application_{$suffix} where sn=?", [$this->sn]);
        }
        return self::fmtApplyStatus($status);
    }

    /**
     * @param $status
     * @return int
     */
    public static function fmtApplyStatus($status): int
    {
        /**
         * 2 审核中
         * 4 放款中
         * 6 放款失败
         * 8 风控拒绝
         *
         * 10 还款期
         * 11 逾期
         * 20 减免结清
         * 30 逾期结清  (优先)
         * 40 展期结清
         * 50 正常结清
         */
        $statusMap = [
            10 => 2,
            11 => 2,
            12 => 2,
            13 => 2,
            14 => 2,
            20 => 8,
            21 => 8,
            30 => 4,
            31 => 4,
            32 => 4,
            33 => 6,
            40 => 10,
            50 => 11,
            60 => 50,
            61 => 50,
            62 => 30,
            63 => 50,
        ];
        return $statusMap[$status] ?? 2;
    }
}