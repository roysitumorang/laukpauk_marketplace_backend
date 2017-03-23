<?php

declare(strict_types=1);

use Phalcon\Debug;
use Phalcon\Di\FactoryDefault;
use Phalcon\Logger\Adapter\File;
use Phalcon\Mvc\Application;

define('APP_PATH', realpath('..') . '/');

register_shutdown_function(function() {
	session_write_close();
	$logger         = new File(APP_PATH . 'apps/logs/perf.log');
	$execution_time = (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000;
	$logger->log(sprintf('%s | %s | processed in %s ms | memory usage %s MB', $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $execution_time, memory_get_peak_usage(true) / 1048576));
});

/**
 * Enable framework debugger
 */
$debug = new Debug;
$debug->listen();

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault;

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
			'path'      => APP_PATH . 'apps/frontend/Module.php',
		],
		'v1' => [
			'className' => 'Application\Api\V1\Module',
			'path'      => APP_PATH . 'apps/api/v1/Module.php',
		],
		'v2' => [
			'className' => 'Application\Api\V2\Module',
			'path'      => APP_PATH . 'apps/api/v2/Module.php',
		],
		'backend' => [
			'className' => 'Application\Backend\Module',
			'path'      => APP_PATH . 'apps/backend/Module.php',
		],
	]);

	// Handle the request
	$response = $application->handle();

	$response->send();
} catch (Throwable $e) {
	echo $e->getMessage() . ' @file: ' . $e->getFile() . ', line: ' . $e->getLine();
}