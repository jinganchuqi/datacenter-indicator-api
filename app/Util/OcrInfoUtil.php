<?php

namespace App\Util;
class OcrInfoUtil
{
    public static $states_map = [
        'AGU' => 'AGUASCALIENTES',
        'BC' => 'BAJA CALIFORNIA',
        'BCS' => 'BAJA CALIFORNIA SUR',
        'CAM' => 'CAMPECHE',
        'CDMX' => 'CIUDAD DE MEXICO',
        'CHIS' => 'CHIAPAS',
        'CHIH' => 'CHIHUAHUA',
        'COAH' => 'COAHUILA',
        'COL' => 'COLIMA',
        'DGO' => 'DURANGO',
        'GTO' => 'GUANAJUATO',
        'GRO' => 'GUERRERO',
        'HGO' => 'HIDALGO',
        'JAL' => 'JALISCO',
        'MEX' => 'ESTADO DE MEXICO',
        'MICH' => 'MICHOACAN',
        'MOR' => 'MORELOS',
        'NAY' => 'NAYARIT',
        'NL' => 'NUEVO LEON',
        'OAX' => 'OAXACA',
        'PUE' => 'PUEBLA',
        'QRO' => 'QUERETARO',
        'QROO' => 'QUINTANA ROO',
        'SLP' => 'SAN LUIS POTOSI',
        'SIN' => 'SINALOA',
        'SON' => 'SONORA',
        'TAB' => 'TABASCO',
        'TAMPS' => 'TAMAULIPAS',
        'TLAX' => 'TLAXCALA',
        'VER' => 'VERACRUZ',
        'YUC' => 'YUCATAN',
        'ZAC' => 'ZACATECAS',
    ];

    /**
     * @param $address
     * @return null[]
     */
    public static function getOcrInfoByAddress($address): array
    {
        $result = [
            'street' => null,
            'number' => null,
            'manzana' => null,
            'lote' => null,
            'colonia' => null,//街道
            'postal_code' => null,
            'municipio' => null,//市
            'state' => null,//州
        ];
        if (empty($address)) {
            return $result;
        }

        $states_map = self::$states_map;

        // 1. 统一大写 + 清洗特殊字符
        $addr = strtoupper($address);
        $addr = preg_replace('/[\.#]/', '', $addr); // 去掉. #
        $addr = preg_replace('/\s+/', ' ', trim($addr));

        // 2. 邮编 （5位数字最稳定）
        if (preg_match('/\b(\d{5})\b/', $addr, $m)) {
            $result['postal_code'] = $m[1];
        }

        // 3. 州
        foreach ($states_map as $abbr => $full) {
            if (preg_match('/\b' . $abbr . '\b/', $addr)) {
                $result['state'] = $full;
                break;
            }
        }

        // 4. municipio / alcaldía
        if (preg_match('/\d{5}\s+([A-Z\. ]+),/', $addr, $m)) {
            $result['municipio'] = trim($m[1]);
        }

        // 5. colonia
        if (preg_match('/\b(COLONIA|COL|FRACCIONAMIENTO|FRACC)\s+([A-Z ]+)\s+\d{5}/', $addr, $m)) {
            $result['colonia'] = trim($m[2]);
        }

        // 6. manzana
        if (preg_match('/\b(MZ|MANZANA)\s*(\d+)/', $addr, $m)) {
            $result['manzana'] = $m[2];
        }

        // 7. lote
        if (preg_match('/\b(LT|LOTE)\s*(\d+)/', $addr, $m)) {
            $result['lote'] = $m[2];
        }

        // 8. number 门牌
        if (preg_match('/\s(\d+)\s+(COLONIA|COL|FRACCIONAMIENTO|FRACC|MZ|MANZANA|LOTE|LT)/', $addr, $m)) {
            $result['number'] = $m[1];
        }

        // 9. street
        if (preg_match('/\b(CALLE|C|AVENIDA|AV|BLVD|BOULEVARD|CAMINO)\s+([A-Z0-9 ]+)/', $addr, $m)) {
            $street = trim($m[2]);
            $street = preg_replace('/\b(MZ|MANZANA|LT|LOTE|COLONIA|COL|FRACCIONAMIENTO|FRACC)\b.*/', '', $street);
            $result['street'] = trim($street);
        }

        return $result;
    }
}