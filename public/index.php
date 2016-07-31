<?php

use Phalcon\Debug;
use Phalcon\Mvc\Application;
use Phalcon\Di\FactoryDefault;

define('APP_PATH', realpath('..') . '/');

/**
 * Enable framework debugger
 */
$debug = new Debug();
$debug->listen();

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();

/**
 * Read the configuration
 */
$config = require APP_PATH . 'apps/config/config.php';

/**
 * Read the services
 */
require APP_PATH . 'apps/config/services.php';

// Specify routes for modules
// More information how to set the router up https://docs.phalconphp.com/en/latest/reference/routing.html
require APP_PATH . 'apps/config/routes.php';

try {
    // Create an application
    $application = new Application($di);

    // Register the installed modules
    $application->registerModules([
        'frontend' => [
            'className' => 'Application\Frontend\Module',
            'path' => APP_PATH . 'apps/frontend/Module.php',
        ],
        'backend' => [
            'className' => 'Application\Backend\Module',
            'path' => APP_PATH . 'apps/backend/Module.php',
        ],
    ]);

    // Handle the request
    $response = $application->handle();

    $response->send();
} catch (\Throwable $e) {
    echo $e->getMessage();
} catch (\Exception $e) {
    echo $e->getMessage();
}