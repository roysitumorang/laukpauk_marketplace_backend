<?php

declare(strict_types=1);

use Phalcon\Debug;
use Phalcon\Di\FactoryDefault;
use Phalcon\Exception;
use Phalcon\Mvc\Application;

$request_uri = filter_input(INPUT_GET, '_url');

if (preg_match('#^/api/v\d/(buyer|merchant)/[a-z\d]{32}#', $request_uri)) {
	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL     => 'http://laukpauk.id:3001' . $request_uri,
		CURLOPT_HEADER  => 0,
		CURLOPT_TIMEOUT => 30,
	]);
	$custom_header = ['X-Requested-With: ' . filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH')];
	$authorization = filter_input(INPUT_SERVER, 'HTTP_AUTHORIZATION');
	$user_data     = filter_input(INPUT_SERVER, 'HTTP_USER_DATA');
	if ($authorization) {
		$custom_header[] = 'Authorization: ' . $authorization;
	}
	if ($user_data) {
		$custom_header[] = 'User-Data: ' . $user_data;
	}
	switch (filter_input(INPUT_SERVER, 'REQUEST_METHOD')) {
		case 'POST':
			$custom_header[] = 'Content-Type: ' . filter_input(INPUT_SERVER, 'CONTENT_TYPE');
			curl_setopt_array($curl, [
				CURLOPT_POST       => 1,
				CURLOPT_POSTFIELDS => json_encode(json_decode(file_get_contents('php://input'))),
			]);
			break;
		case 'OPTIONS':
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
			break;
	}
	if ($custom_header) {
		curl_setopt($curl, CURLOPT_HTTPHEADER, $custom_header);
	}
	curl_exec($curl);
	curl_close($curl);
	exit;
}

define('APP_PATH', realpath('..') . '/');

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
		'v3' => [
			'className' => 'Application\Api\V3\Module',
			'path'      => APP_PATH . 'apps/api/v3/Module.php',
		],
		'backend' => [
			'className' => 'Application\Backend\Module',
			'path'      => APP_PATH . 'apps/backend/Module.php',
		],
	]);

	// Handle the request
	$application->handle($request_uri)->send();
} catch (Exception $e) {
	echo $e->getMessage() . ' @file: ' . $e->getFile() . ', line: ' . $e->getLine();
}
