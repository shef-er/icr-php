<?php
declare(strict_types=1);

use App\Handler\HttpErrorHandler;
use DI\ContainerBuilder;
use Slim\App;

// require_once __DIR__ . '/config/defines.php';
require_once __DIR__ . '/vendor/autoload.php';

define('ROOT_DIR', dirname(realpath(__FILE__)));

if (is_readable(__DIR__ . '/.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/');
    $dotenv->load();
}


// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

// Set up dependencies
$containerBuilder->addDefinitions(__DIR__ . '/config/dependencies.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

/**
 * Instantiate the app
 * 
 * @var App $app
 */
$app = $container->get(App::class);

// /** 
//  * @var bool $displayErrorDetails
//  */
// $displayErrorDetails = $container->get('settings')['displayErrorDetails'];

// /** 
//  * @var bool $logErrors
//  */
// $logErrors = $container->get('settings')['logErrors'];

// /** 
//  * @var bool $logErrorDetails
//  */
// $logErrorDetails = $container->get('settings')['logErrorDetails'];

// /**
//  * @var HttpErrorHandler $errorHandler
//  */
// $errorHandler = $container->get(HttpErrorHandler::class);

// $errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);
// $errorMiddleware->setDefaultErrorHandler($errorHandler);

// Register middleware
(require_once __DIR__ . '/config/middleware.php')($app, $container);

// Register routes
(require_once __DIR__ . '/config/routes.php')($app, $container);

// Run app
$app->run();

// // Create Request object from globals
// $serverRequestCreator = ServerRequestCreatorFactory::create();
// $request = $serverRequestCreator->createServerRequestFromGlobals();
// $shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
// register_shutdown_function($shutdownHandler);
// $app->run($request);
