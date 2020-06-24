<?php
declare(strict_types=1);

use App\Action\MainAction;
use App\Action\DeployAction;
use App\Action\LoginAction;
use App\Action\LogoutAction;
use App\Action\UsersAction;
use App\GoogleClient;
use App\Handler\HttpErrorHandler;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;

return [

    'settings' => (require_once __DIR__ . '/settings.php'),

    App::class => function (ContainerInterface $c)
    {
        AppFactory::setContainer($c);
        $app = AppFactory::create();

        return $app;
    },

    ResponseFactoryInterface::class => function (ContainerInterface $c)
    {
        return $c->get(App::class)->getResponseFactory();
    },

    HttpErrorHandler::class => function (ContainerInterface $c)
    {
        $callableResolver = $c->get(App::class)->getCallableResolver();
        $responseFactory = $c->get(ResponseFactoryInterface::class);

        return new HttpErrorHandler($callableResolver, $responseFactory);
    },

    ErrorMiddleware::class => function (ContainerInterface $c)
    {
        $config = (array)$c->get('settings')['errors'];

        /**
         * @var App $app
         */
        $app = $c->get(App::class);

        /**
         * @var HttpErrorHandler $errorHandler
         */
        $errorHandler = $c->get(HttpErrorHandler::class);

        $errorMiddleware = new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            (bool)$config['display_error_details'],
            (bool)$config['log_errors'],
            (bool)$config['log_error_details']
        );
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        return $errorMiddleware;
    },

    LoggerInterface::class => function (ContainerInterface $c)
    {
        $config = $c->get('settings')['logger'];
        
        $logger = new Logger($config['name']);

        $processor = new UidProcessor();
        $logger->pushProcessor($processor);

        $handler = new StreamHandler($config['path'], $config['level']);
        $logger->pushHandler($handler);

        return $logger;
    },

    Connection::class => function (ContainerInterface $c)
    {
        $config = (array)$c->get('settings')['database'];

        $connection_params = [
            'driver'    => (string)$config['driver'],
            'host'      => (string)$config['host'],
            'user'      => (string)$config['user'],
            'password'  => (string)$config['password'],
            'dbname'    => (string)$config['dbname'],
        ];
        return DriverManager::getConnection($connection_params);
    },

    QueryBuilder::class => function (ContainerInterface $c)
    {
        /**
         * @var Connection $db
         */
        $db = $c->get(Connection::class);
        return $db->createQueryBuilder();
    },

    GoogleClient::class => function(ContainerInterface $c)
    {
        $config = (array)$c->get('settings')['google_client'];

        return new GoogleClient(
            (array)$config['config'],
            (array)$config['scopes'],
            (string)$config['redirect_uri']
        );
    },



    MainAction::class => function (ContainerInterface $c, LoggerInterface $l)
    {
        return new MainAction($c, $l);
    },

    DeployAction::class => function (ContainerInterface $c, LoggerInterface $l)
    {
        return new DeployAction($c, $l);
    },

    LoginAction::class => function (ContainerInterface $c, LoggerInterface $l)
    {
        return new LoginAction($c, $l);
    },

    LogoutAction::class => function (ContainerInterface $c, LoggerInterface $l)
    {
        return new LogoutAction($c, $l);
    },

    UsersAction::class => function (ContainerInterface $c, LoggerInterface $l)
    {
        return new UsersAction($c, $l);
    },

];
