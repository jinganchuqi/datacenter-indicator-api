<?php

namespace App\Common\Guzzle;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * 选项
     * @var array
     */
    public array $options = [
        'verify' => false, // 不验证SSL
    ];

    /**
     * 请求头
     * @var array
     */
    public array $headers = [
        'Content-Type' => 'application/json; charset=utf-8'
    ];

    /**
     * 代理配置
     * @var array
     */
    public array $proxy = [];

    /**
     * @var string
     */
    private string $domain;

    /**
     * @var GuzzleClient
     */
    protected GuzzleClient $http;

    /**
     * @var bool
     */
    protected bool $recordLog = true;

    /**
     * 响应body
     * @var string
     */
    public string $responseBody = '';

    /**
     * http状态
     * @var int|null
     */
    public int|null $httpStatus = null;

    /**
     * @param string $domain
     * @param int|float $timeout
     */
    public function __construct(string $domain = '', int|float $timeout = 15)
    {
        $this->domain = $domain;
        $this->http = new GuzzleClient([
            'base_uri' => $this->domain,
            'timeout' => $timeout
        ]);
    }

    /**
     * @param array $headers
     * @return Client
     */
    public function setHeaders(array $headers): Client
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }


    /**
     * @param bool $recordLog
     * @return Client
     */
    public function setRecordLog(bool $recordLog): Client
    {
        $this->recordLog = $recordLog;
        return $this;
    }

    /**
     * 设置代理
     * @param string $proxy
     * @return $this
     */
    public function setHttpProxy(string $proxy): Client
    {
        $this->options['proxy']['http'] = $proxy;
        return $this;
    }

    /**
     * 设置代理
     * @param string $proxy
     * @return $this
     */
    public function setHttpsProxy(string $proxy): Client
    {
        $this->options['proxy']['https'] = $proxy;
        return $this;
    }

    /**
     * @param string $method
     * @param string $uri
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function request(string $method, string $uri): ResponseInterface
    {
        $sTime = microtime(true);
        $this->options['headers'] = $this->headers;
        $response = $this->http->request($method, $uri, $this->options);
        $eTime = microtime(true);
        $useTime = bcsub((string)$eTime, (string)$sTime, 6);
        $this->httpStatus = $response->getStatusCode();
        $this->responseBody = $response->getBody()->getContents();

        /* if (
             !str_contains($uri, 'credit/data/feature')
             && !str_contains($uri, '/mapping/')
             && $this->recordLog
         ) {
             AppLog::info("请求日志", [
                 'url' => "$this->domain$uri",
                 'useTime' => "{$useTime}s",
                 'httpStatus' => $this->httpStatus,
                 'options' => $this->options,
                 'responseBody' => $this->responseBody
             ], 'Request');
         }*/

        return $response;
    }

    /**
     * @param string $uri
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function get(string $uri): ResponseInterface
    {
        return $this->request('GET', $uri);
    }

    /**
     * @param string $uri
     * @param mixed $params
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function post(string $uri, mixed $params): ResponseInterface
    {
        $this->options['body'] = $this->bodyFormat($params);
        return $this->request('POST', $uri);
    }

    /**
     * body格式化
     * @param array $params
     * @return string
     */
    private function bodyFormat(mixed $params): mixed
    {
        if (is_array($params)) {
            if (str_contains($this->headers['Content-Type'], 'application/json')) {
                return $this->bodyJson($params);
            }
            if (str_contains($this->headers['Content-Type'], 'application/x-www-form-urlencoded')) {
                return $this->bodyUrlencoded($params);
            }
        }
        return $params;
    }

    /**
     * json
     * @param array $params
     * @return string
     */
    private function bodyJson(array $params): string
    {
        return json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * 表单
     * @param array $params
     * @return string
     */
    private function bodyUrlencoded(array $params): string
    {
        $body = '';
        foreach ($params as $key => $val) {
            $body .= "&$key=$val";
        }
        return substr($body, 1);
    }
}
