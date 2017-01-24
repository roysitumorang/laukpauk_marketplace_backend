<?php

namespace Application\Frontend;

use Application\Models\User;
use Phalcon\DiInterface;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Loader;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;

class Module implements ModuleDefinitionInterface {
	/**
	 * Register a specific autoloader for the module.
	 */
	function registerAutoloaders(DiInterface $di = null) {
		$application = $di->getConfig()->application;
		$loader      = new Loader();
		$loader->registerNamespaces([
			'Application\Frontend\Controllers' => APP_PATH . 'apps/frontend/controllers/',
			'Application\Frontend\Forms'       => APP_PATH . 'apps/frontend/forms',
			'Application\Models'               => $application->modelsDir,
			'Application\Plugins'              => $application->pluginsDir,
			'Phalcon'                          => $application->libraryDir,
		]);
		$loader->register();
	}

	/**
	 * Register specific services for the module.
	 */
	function registerServices(DiInterface $di) {
		/**
		 * Start the session the first time some component request the session service
		 */
		$di->setShared('session', function() use($di) {
			$session = new \Phalcon\Session\Adapter\Database([
				'db'    => $di->getDb(),
				'table' => 'sessions',
			]);
			$session->start();
			return $session;
		});

		// Registering a dispatcher
		$di->set('dispatcher', function() {
			$dispatcher    = new Dispatcher;
			$eventsManager = new EventsManager;
			$dispatcher->setDefaultNamespace('Application\Frontend\Controllers');
			$eventsManager->attach('dispatch:beforeException', function(Event $event, $dispatcher, $exception) {
				if ($exception instanceof DispatchException && in_array($exception->getCode(), [Dispatcher::EXCEPTION_HANDLER_NOT_FOUND, Dispatcher::EXCEPTION_ACTION_NOT_FOUND])) {
					$dispatcher->forward([
						'controller' => 'home',
						'action'     => 'notFound',
					]);
				} else {
					$dispatcher->forward([
						'controller' => 'home',
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
			$view = new View();
			$view->setViewsDir(APP_PATH . 'apps/frontend/views/');
			$view->registerEngines([
				'.volt' => function($view, $di) {
					$volt = new Volt($view, $di);
					$volt->setOptions([
						'compiledPath'      => APP_PATH . 'apps/frontend/cache/',
						'compiledSeparator' => '_',
					]);
					$volt->getCompiler()
						->addFunction('is_a', 'is_a')
						->addFunction('count', 'count')
						->addFunction('number_format', 'number_format')
						->addFunction('date', 'date')
						->addFunction('strtotime', 'strtotime');

					return $volt;
				},
			]);

			return $view;
		});

		$di->set('currentUser', function() use($di) {
			return User::findFirst($di->getSession()->get('user_id'));
		});

		$di->set('currentDatetime', function() use($di) {
			$current_datetime = DateTimeImmutable::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
			return $current_datetime->setTimezone(new DateTimeZone($di->getConfig()->timezone));
		});
	}
}