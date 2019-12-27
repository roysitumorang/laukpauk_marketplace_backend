<?php

namespace Application\Backend;

use Application\Models\User;
use Phalcon\Di\DiInterface;
use Phalcon\Dispatcher\Exception as DispatcherException;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Loader;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatchException;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Session\Adapter\Redis;
use Phalcon\Session\Manager;
use Phalcon\Storage\AdapterFactory;
use Phalcon\Storage\SerializerFactory;

class Module implements ModuleDefinitionInterface {
	/**
	 * Register a specific autoloader for the module.
	 */
	function registerAutoloaders(?DiInterface $di = null) {
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
			$options = [
				'host' => '127.0.0.1',
				'port' => 6379,
				'index' => '1',
			];
			$session = new Manager;
			$serializer = new SerializerFactory;
			$adapter = new AdapterFactory($serializer);
			$redis = new Redis($adapter, $options);
			$session->setAdapter($redis);
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
				if ($exception instanceof DispatchException || in_array($exception->getCode(), [DispatcherException::EXCEPTION_HANDLER_NOT_FOUND, DispatcherException::EXCEPTION_ACTION_NOT_FOUND])) {
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
				'.volt' => function($view) {
					$volt = new Volt($view, $this);
					$volt->setOptions([
						'path'      => APP_PATH . 'apps/backend/cache/',
						'separator' => '_',
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
						})
						->addFilter('orElse', function($resolvedArgs, $exprArgs) use($volt) {
							$compiler       = $volt->getCompiler();
							$firstArgument  = $compiler->expression($exprArgs[0]['expr']);
							$secondArgument = $compiler->expression($exprArgs[1]['expr']);
							return $firstArgument . '?:' . $secondArgument;
						});
					return $volt;
				},
			]);
			return $view;
		});

		$di->set('currentUser', fn() => User::findFirstById($this->getSession()->get('user_id')));
	}
}
