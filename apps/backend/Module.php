<?php

namespace Application\Backend;

use Application\Models\User;
use Phalcon\Dispatcher;
use Phalcon\DiInterface;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Loader;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatchException;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Session\Adapter\Database;

class Module implements ModuleDefinitionInterface {
	/**
	 * Register a specific autoloader for the module.
	 */
	function registerAutoloaders(DiInterface $di = null) {
		$application = $di->getConfig()->application;
		$loader      = new Loader;
		$loader->registerNamespaces([
			'Application\Backend\Controllers' => APP_PATH . 'apps/backend/controllers/',
			'Application\Backend\Forms'       => APP_PATH . 'apps/backend/forms',
			'Application\Models'              => $application->modelsDir,
			'Application\Plugins'             => $application->pluginsDir,
			'Phalcon'                         => $application->libraryDir,
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
		$di->setShared('session', function() {
			$session = new Database([
				'db'    => $this->getDb(),
				'table' => 'sessions',
			]);
			$session->start();
			return $session;
		});

		// Registering a dispatcher
		$di->set('dispatcher', function() {
			// Create an EventsManager
			$dispatcher    = new MvcDispatcher;
			$eventsManager = new EventsManager;
			$dispatcher->setDefaultNamespace('Application\Backend\Controllers');
			$eventsManager->attach('dispatch:beforeDispatchLoop', function(Event $event, $dispatcher) {
				$oldParams = $dispatcher->getParams();
				$newParams = [];
				foreach ($oldParams as $key => $value) {
					if (!strstr($value, ':')) {
						$newParams[$key] = $value;
						continue;
					}
					list($newKey, $newValue) = explode(':', $value);
					$newParams[$newKey]      = $newValue;
				}
				$dispatcher->setParams($newParams);
			});
			$eventsManager->attach('dispatch:beforeException', function(Event $event, $dispatcher, \Exception $exception) {
				if ($exception instanceof DispatchException || in_array($exception->getCode(), [Dispatcher::EXCEPTION_HANDLER_NOT_FOUND, Dispatcher::EXCEPTION_ACTION_NOT_FOUND])) {
					$dispatcher->forward([
						'controller' => 'home',
						'action'     => 'route404',
					]);
					return false;
				}
				return true;
			});
			$dispatcher->setEventsManager($eventsManager);
			return $dispatcher;
		});

		// Registering the view component
		$di->set('view', function() {
			$view = new View;
			$view->setViewsDir(APP_PATH . 'apps/backend/views/');
			$view->registerEngines([
				'.volt' => function($view, $di) {
					$volt = new Volt($view, $di);
					$volt->setOptions([
						'compiledPath'      => APP_PATH . 'apps/backend/cache/',
						'compiledSeparator' => '_',
					]);
					$volt->getCompiler()
						->addFunction('ctype_digit', 'ctype_digit')
						->addFunction('in_array', 'in_array')
						->addFunction('count', 'count')
						->addFilter('count', function($resolvedArgs, $exprArgs) {
							return 'count(' . $resolvedArgs . ')';
						})
						->addFilter('number_format', function($resolvedArgs, $exprArgs) {
							return 'number_format(' . $resolvedArgs . ')';
						})
						->addFilter('datetime', function($resolvedArgs, $exprArgs) {
							return 'date("d M Y H:i", strtotime(' . $resolvedArgs . '))';
						})
						->addFilter('strtr', function($resolvedArgs, $exprArgs) {
							return 'strtr(' . $resolvedArgs . ')';
						});
					return $volt;
				},
			]);
			return $view;
		});

		$di->set('currentUser', function() {
			return User::findFirst($this->getSession()->get('user_id'));
		});
	}
}
