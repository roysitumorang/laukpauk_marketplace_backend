<?php

namespace Application\Api;

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\DiInterface;
use Phalcon\Http\Response;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface {
	/**
	 * Register a specific autoloader for the module.
	 */
	function registerAutoloaders(DiInterface $di = null) {
		$application = $di->getConfig()->application;
		$loader      = new Loader;
		$loader->registerNamespaces([
			'Application\Api\Controllers' => APP_PATH . 'apps/api/controllers/',
			'Application\Models'          => $application->modelsDir,
			'Application\Plugins'         => $application->pluginsDir,
			'Phalcon'                     => $application->libraryDir,
		]);
		$loader->register();
	}

	/**
	 * Register specific services for the module.
	 */
	function registerServices(DiInterface $di) {
		// Registering a dispatcher
		$di->set('dispatcher', function() {
			$dispatcher = new Dispatcher;
			$dispatcher->setDefaultNamespace('Application\Api\Controllers');
			return $dispatcher;
		});

		// Registering the view component
		$di->set('view', function() {
			$view = new View;
			$view->disable();
			return $view;
		});

		// Registering the response component
		$di->set('response', function() {
			$response = new Response;
			$response->setStatusCode(200, 'OK');
			$response->setContentType('application/json', 'UTF-8');
			return $response;
		});
	}
}