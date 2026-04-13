<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    #[Inject]
    protected StdoutLoggerInterface $logger;

    /**
     * @param Throwable $throwable
     * @param ResponseInterface $response
     * @return MessageInterface|ResponseInterface
     */
    public function handle(Throwable $throwable, ResponseInterface $response): MessageInterface|ResponseInterface
    {
        $code = $throwable->getCode();
        if (empty($code)) {
            $code = 500;
        }

        $this->logger->error(sprintf('%s:%s %s:%s',
            $code,
            $throwable->getMessage(),
            $throwable->getFile(),
            $throwable->getLine()
        ));

        $this->stopPropagation();
        $message = formatErrorMessage(
            $code,
            $throwable->getMessage()
        );

        return $response->withHeader("Content-Type", "application/json")->withBody(new SwooleStream(json_encode([
            "code" => $code,
            "message" => $message,
        ])));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
