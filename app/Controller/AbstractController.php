<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Common\RequestAndResponseEntity;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;

    /**
     * @param array|object|null $data
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function success(array|object|null $data = []): \Psr\Http\Message\ResponseInterface
    {
        return $this->response->json([
            'code' => 0,
            'message' => 'ok',
            'data' => !empty($data) ? $data : new \stdClass(),
        ]);
    }

    /**
     * @param int $code
     * @param string $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function fail(int $code, string $message = ''): \Psr\Http\Message\ResponseInterface
    {
        if (empty($message)) {
            $message = formatErrorMessage($code);
        }
        return $this->response->json([
            'code' => $code,
            'message' => $message,
            'data' => new \stdClass(),
        ]);
    }
}
