<?php

namespace App\Constant;
class InstallChannelConst
{

    /**
     * 渠道转换
     * @param $channel
     * @return mixed|string
     */
    public static function format($channel): mixed
    {
        if (empty($channel)) {
            return null;
        }

        $lowerChannel = strtolower($channel);
        if (str_contains($lowerChannel, "organic")) {
            return "ORGANIC";
        }

        if (str_contains($lowerChannel, "google")) {
            return "GG";
        }

        if (str_contains($lowerChannel, "apple")) {
            return "ASA";
        }

        if (str_contains($lowerChannel, "tiktok")) {
            return "TT";
        }

        if (str_contains($lowerChannel, "facebook")) {
            return "FB";
        }

        if (str_contains($lowerChannel, "instagram")) {
            return "FB";
        }

        if (str_contains($lowerChannel, "messenger")) {
            return "FB";
        }

        if (str_contains($lowerChannel, "sms")) {
            return "SMS";
        }

        if (str_contains($lowerChannel, "kuai")) {
            return "KW";
        }
        return $channel;
    }
}