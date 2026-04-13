<?php
declare(strict_types=1);

use Hyperf\HttpServer\Router\Router;

Router::get('/api/ping', [App\Controller\Api\ApiController::class, 'ping']);