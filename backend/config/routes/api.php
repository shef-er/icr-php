<?php
declare(strict_types=1);

use App\Action\DeployAction;
use App\Action\LoginAction;
use App\Action\LogoutAction;
use App\Action\MainAction;
use App\Action\TestAction;
use App\Action\UsersAction;
use Slim\Routing\RouteCollectorProxy;

return function (RouteCollectorProxy $proxy) {

        $proxy->get(
            '/',
            MainAction::class
        );

        $proxy->post(
            '/users',
            UsersAction::class
        );

        $proxy->get(
            '/login',
            LoginAction::class
        );

        $proxy->post(
            '/login',
            LoginAction::class
        );

        $proxy->get(
            '/logout',
            LogoutAction::class
        );

        $proxy->get(
            '/deploy/{target:[a-z]+}',
            DeployAction::class
        );

        // TODO: remove temporary testing route
        $proxy->get(
            '/test',
            TestAction::class
        );

    };
