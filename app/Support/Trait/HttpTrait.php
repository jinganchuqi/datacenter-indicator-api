<?php

namespace App\Support\Trait;

use App\Common\Guzzle\Client;
use App\Exception\AppException;
use WLib\WLog;

trait HttpTrait
{
    /**
     * @param string $path
     * @param array $data
     * @param array $header
     * @param array $parse
     * @param array $proxy
     * @return mixed
     * @throws AppException
     */
    public static function request(
        string   $path,
        array    $data = [],
        array    $header = [],
        array    $parse = [
            'name' => 'code',
            'value' => 0,
        ], array $proxy = []): mixed
    {
        try {
            $client = (new Client(self::$url));

            if (!empty($header)) {
                $client->setHeaders($header);
            }

            if (!empty($proxy['http'])) {
                $client->setHttpProxy($proxy['http']);
            }

            if (!empty($proxy['https'])) {
                $client->setHttpsProxy($proxy['https']);
            }

            if (!empty($data)) {
                $client->post($path, $data);
            } else {
                $client->get($path);
            }

        } catch (\Throwable $throwable) {
            throw new AppException("请求失败:{$throwable->getMessage()}");
        }

        $status = $client->httpStatus;
        if ($status !== 200) {
            throw new AppException("请求失败:{$status}");
        }

        $resp = json_decode($client->responseBody, true);
        if (empty($resp)) {
            throw new AppException("响应失败");
        }

        if (empty($parse)) {
            return $resp;
        }

        if (arr_get($resp, $parse['name']) != $parse['value']) {
            throw new AppException(arr_get($resp, 'message', '请求失败'));
        }

        return $resp['data'];
    }


    /**
     * @param $requestId
     * @param $url
     * @param array $payload
     * @param array $headers
     * @return array
     */
    protected function gzCurl($requestId, $url, array $payload, array $headers): array
    {
        try {
            //$data = gzencode(json_encode($payload, 320), 9);

            $data = json_encode($payload, 320);

            // 初始化 cURL
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // 设置 POST
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            // 忽略 SSL 证书验证 (对应 curl -k)
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            // 返回结果而不是直接输出
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // 执行请求
            $response = curl_exec($ch);

            // 获取code
            $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            WLog::info("执行curl上传响应", [
                'requestId' => $requestId,
                'response' => $response,
                'httpStatus' => $httpStatus,
            ]);

            // 关闭 cURL
            curl_close($ch);

            return [
                $httpStatus,
                $response,
            ];
        } catch (\Throwable $e) {
            tryCatchLog($e, "上传数据");
        }

        return [
            -1,
            null
        ];
    }
}