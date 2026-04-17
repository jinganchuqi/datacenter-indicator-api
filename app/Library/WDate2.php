<?php

namespace App\Library;

use WLib\WDate;

class WDate2
{
// @see https://zh.wikipedia.org/wiki/ISO_3166-1
    // @see https://www.php.net/manual/zh/timezones.africa.php

    private static array $map = [
        'uk' => 'Europe/London',
        'cn' => 'Asia/Shanghai',
        'id' => 'Asia/Jakarta',         //印尼
        'ng' => 'Africa/Lagos',         // 尼日
        'in' => 'Asia/Kolkata',          // 印度
        'mx' => 'America/Costa_Rica',    //墨西哥用哥斯达黎加 UTC-6
        'tz' => 'Africa/Dar_es_Salaam',  // 坦桑尼亚
        'gh' => 'Africa/Accra',          // 加纳
        'rw' => 'Africa/Kigali',         // 卢旺达
        'lk' => 'Asia/Colombo',          // 斯里兰卡
        'ao' => 'Africa/Luanda',        // 安哥拉
        'bd' => 'Asia/Dhaka',          // 孟加拉
        'cl' => 'America/Sao_Paulo', //智利用圣保罗时间 UTC -3
        'pe' => 'America/Lima',//秘鲁 UTC-5
        'ar' => 'America/Argentina/Cordoba',//阿根廷 UTC-3
        'bo' => 'America/La_Paz',//玻利维亚 UTC-4
        'do' => 'America/Santo_Domingo',//多米尼加 UTC-4
        'ec' => 'America/Guayaquil',//厄瓜多尔 -5:00
        'co' => 'America/Bogota',//哥伦比亚 -5:00
        've' => 'America/Caracas',//委内瑞拉 -5:00
    ];

    private \DateTime $dateTime;

    public function __construct(string $countryISO2 = '')
    {
        $this->dateTime = new \DateTime();
        if ($countryISO2) {
            $this->setCountry($countryISO2);
        }
    }

    public static function getInstance($country = 'cn'): static
    {
        return new static($country);
    }


    /**
     * @param string $country
     * @return bool
     */
    public static function countryIsValid(string $country): bool
    {
        return isset(self::$map[$country]);
    }

    public function setCountry(string $countryISO2): static
    {
        $this->dateTime->setTimezone(new \DateTimeZone(self::$map[$countryISO2]));
        return $this;
    }

    /**
     * @param int $timestamp
     */
    public function setTimestamp(int $timestamp): static
    {
        $this->dateTime->setTimestamp($timestamp);
        return $this;
    }

    /**
     * 返回对应时区的 Ymd 格式
     * @param int $timestamp
     * @param string $zone
     * @return string
     */
    public function format($format = 'Y-m-d H:i:s'): string
    {
        return $this->dateTime->format($format);
    }

    /**
     * 获取时间戳
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->dateTime->getTimestamp();
    }

    /**
     * @param string $date 2021-01-01 or 2021-01-01 23:25:56
     * @return $this
     */
    public function setDateTimeStr(string $datetime): static
    {
        $d = str_replace([" ", ":"], "-", $datetime);
        $a = explode("-", $d);

        $this->dateTime->setDate((int)$a[0], (int)$a[1], (int)$a[2]);

        if (count($a) > 3) {
            $this->dateTime->setTime((int)$a[3], (int)$a[4], (int)$a[5]);
        } else {
            $this->dateTime->setTime(0, 0, 0);
        }
        return $this;
    }

    /**
     * 设置为今天开始的时间
     */
    public function dayBegin(): static
    {
        $this->setDateTimeStr($this->format("Y-m-d"));
        return $this;
    }

    /**
     * 设置为今天结束的时间
     */
    public function dayEnd(): static
    {
        $this->setDateTimeStr($this->format("Y-m-d 23:59:59"));
        return $this;
    }

    /**
     * @param string $modifier
     * @return $this
     */
    public function modify(string $modifier): static
    {
        $this->dateTime->modify($modifier);
        return $this;
    }

    public static function setDefaultTimezone(string $countryISO2): void
    {
        date_default_timezone_set(self::$map[$countryISO2]);
    }
}