<?php
declare(strict_types=1);

use App\Middleware\GuardMiddleware;
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app, Container $c) {

    // CORS Pre-Flight OPTIONS Request Handler
    $app->options(
        '/api/{routes:.+}',
        function (Request $request, Response $response) {
            return $response;
        }
    );

    $app->group('/api', require __DIR__ . '/routes/api.php')
    //    ->add(new GuardMiddleware($c))
    ;

    /*
     * DEPRECATED
     */
    $app->group('', require __DIR__ . '/routes/api_deprecated.php');
    $app->group('/api', require __DIR__ . '/routes/api_deprecated.php');
};
