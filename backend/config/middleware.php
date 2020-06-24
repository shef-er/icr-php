<?php
declare(strict_types=1);

use App\Middleware\SessionMiddleware;
use App\Middleware\CorsMiddleware;
use DI\Container;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;

return function (App $app, Container $c) {
    $app->addRoutingMiddleware();

    $app->addBodyParsingMiddleware();

    $app->add(SessionMiddleware::class);

    $app->add(CorsMiddleware::class);

    $app->add(ErrorMiddleware::class);
};
