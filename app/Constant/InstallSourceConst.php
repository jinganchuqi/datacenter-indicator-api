<?php

namespace App\Constant;
class InstallSourceConst
{

    /**
     * 渠道转换
     * @param $channel
     * @return string|null
     */
    public static function format($channel): ?string
    {
        if (empty($channel)) {
            return null;
        }

        $lowerChannel = strtolower($channel);
        if (str_contains($lowerChannel, "google")) {
            return "google";
        }

        if (str_contains($lowerChannel, "apple")) {
            return "apple";
        }

        if (str_contains($lowerChannel, "tiktok")) {
            return "tiktok";
        }

        if (str_contains($lowerChannel, "facebook")) {
            return "facebook";
        }

        if (str_contains($lowerChannel, "instagram")) {
            return "instagram";
        }

        if (str_contains($lowerChannel, "messenger")) {
            return "messenger";
        }

        if (str_contains($lowerChannel, "organic")) {
            return "organic";
        }

        if (str_contains($lowerChannel, "sms")) {
            return "sms";
        }

        if (str_contains($lowerChannel, "kuai")) {
            return "kwai";
        }

        return $lowerChannel;
    }
}