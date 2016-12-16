<?php

namespace Application\Api\V1;

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\DiInterface;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Http\Response;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatchException;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface {
	/**
	 * Register a specific autoloader for the module.
	 */
	function registerAutoloaders(DiInterface $di = null) {
		$application = $di->getConfig()->application;
		$loader      = new Loader;
		$loader->registerNamespaces([
			'Application\Api\V1\Controllers' => APP_PATH . 'apps/api/v1/controllers/',
			'Application\Models'             => $application->modelsDir,
			'Application\Plugins'            => $application->pluginsDir,
			'Phalcon'                        => $application->libraryDir,
		]);
		$loader->register();
	}

	/**
	 * Register specific services for the module.
	 */
	function registerServices(DiInterface $di) {
		// Registering a dispatcher
		$di->set('dispatcher', function() {
			$dispatcher    = new Dispatcher;
			$eventsManager = new EventsManager;
			$dispatcher->setDefaultNamespace('Application\Api\V1\Controllers');
			$eventsManager->attach('dispatch:beforeException', function(Event $event, $dispatcher, $exception) {
				if ($exception instanceof DispatchException && in_array($exception->getCode(), [Dispatcher::EXCEPTION_HANDLER_NOT_FOUND, Dispatcher::EXCEPTION_ACTION_NOT_FOUND])) {
					$dispatcher->forward([
						'controller' => 'errors',
						'action'     => 'notFound',
					]);
				} else {
					$dispatcher->forward([
						'controller' => 'errors',
						'action'     => 'uncaughtException',
					]);
				}
				return false;
			});
			$dispatcher->setEventsManager($eventsManager);
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
			$response->setHeader('Access-Control-Allow-Origin', '*');
			$response->setHeader('Access-Control-Allow-Methods', 'POST, GET');
			return $response;
		});
	}
}