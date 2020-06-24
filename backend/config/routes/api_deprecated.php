<?php
declare(strict_types=1);

use App\Api;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

return function (RouteCollectorProxy $group) use ($c) {
    /**
     * @var Api $api
     */
    $api = new Api($c);

    /**
     * Action: /[a-z_-]+
     * Method: GET
     */
    $group->get(
        '/{table:jobs|jobs_report|personnel|projects|worker_report}',
        function (Request $request, Response $response, $args) use (&$api) {

            $table_name = $args["table"];
            $result = $api->selectTable(
                $request,
                $response,
                $table_name
            );

            $response->getBody()->write(json_encode($result));
            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');

        }
    );

    /**
     * Action: /rpc/[a-z_-]+
     * Method: GET, POST
     */
    $group->map(
        ['GET', 'POST'], 
        '/rpc/{procedure:[a-z_-]+}', 
        function (Request $request, Response $response, $args) use (&$api) {

            $proc_name = $args['procedure'];
            $result = $api->selectProcedure(
                $request,
                $response,
                $proc_name
            );

            $response->getBody()->write(json_encode($result));
            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');

        }
    );

    /**
     * Action: /worker_report
     * Method: PATCH
     */
    $group->patch(
        '/worker_report',
        function (Request $request, Response $response, $args) {
            return $response;
        }
    );

    /**
     * Action: /worker_report
     * Method: POST
     */
    $group->post(
        '/worker_report',
        function (Request $request, Response $response, $args) use (&$api) {

            $api->upsertWorkerReport(
                $request,
                $response
            );

            return $response
                    ->withStatus(204)
                    ->withHeader('Content-Type', 'application/json');
        }
    );
};
