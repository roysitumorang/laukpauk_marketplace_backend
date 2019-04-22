<?php

namespace Application\Api\V3;

use Application\Models\Role;
use Application\Models\User;
use IntlDateFormatter;
use Phalcon\Cache\Backend\Redis;
use Phalcon\Cache\Frontend\Data as FrontData;
use Phalcon\DiInterface;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Http\Response;
use Phalcon\Loader;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatchException;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View;

class Module implements ModuleDefinitionInterface {
	/**
	 * Register a specific autoloader for the module.
	 */
	function registerAutoloaders(DiInterface $di = null) {
		$application = $di->getConfig()->application;
		$loader      = new Loader;
		$loader->registerNamespaces([
			'Application\Api\V3\Buyer'    => APP_PATH . 'apps/api/v3/buyer/',
			'Application\Api\V3\Merchant' => APP_PATH . 'apps/api/v3/merchant/',
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
			$dispatcher    = new Dispatcher;
			$eventsManager = new EventsManager;
			$dispatcher->setDefaultNamespace('Application\Api\V3\Controllers');
			$eventsManager->attach('dispatch:beforeDispatchLoop', function(Event $event, $dispatcher) {
				$old_params = $dispatcher->getParams();
				$new_params = [];
				foreach ($old_params as $key => $value) {
					if (!strstr($value, ':')) {
						$new_params[$key] = $value;
						continue;
					}
					list($new_key, $new_value) = explode(':', $value);
					$new_params[$new_key]      = $new_value;
				}
				$dispatcher->setParams($new_params);
			});
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
			$response->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS');
			$response->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Range, Content-Disposition, Content-Type, Authorization, User-Data, App-Version');
			$response->setHeader('Access-Control-Allow-Credentials', 'true');
			return $response;
		});

		// Register the cache component
		$di->set('cache', function() {
			$cache = new Redis(new FrontData(['lifetime' => 172800]), [
				'host'       => 'localhost',
				'port'       => 6379,
				'persistent' => true,
				'index'      => 0,
			]);
			return $cache;
		});

		$di->set('session', function() {
			return null;
		});

		$di->set('currentUser', function() {
			$access_token = strtr(filter_input(INPUT_SERVER, 'HTTP_AUTHORIZATION'), ['Bearer ' => '']);
			if (!$access_token) {
				return null;
			}
			$payload = $this->getJsonWebToken()->decode($access_token);
			return User::findFirst(['status = 1 AND api_key = ?0 AND (role_id = ?1 OR role_id = ?2)', 'bind' => [$payload->api_key, Role::MERCHANT, Role::BUYER]]);
		});

		$di->set('post', function() {
			return $this->getRequest()->getJsonRawBody();
		});

		$di->set('server', function() {
			return json_decode($this->getRequest()->getServer('HTTP_USER_DATA'));
		});

		$di->set('dateFormatter', function() {
			return new IntlDateFormatter(
				'id_ID',
				IntlDateFormatter::FULL,
				IntlDateFormatter::NONE,
				$this->getCurrentDatetime()->getTimezone(),
				IntlDateFormatter::GREGORIAN,
				'd MMM yyyy'
			);
		});

		$di->set('dayFormatter', function() {
			return new IntlDateFormatter(
				'id_ID',
				IntlDateFormatter::FULL,
				IntlDateFormatter::NONE,
				$this->getCurrentDatetime()->getTimezone(),
				IntlDateFormatter::GREGORIAN,
				'EEEE'
			);
		});
	}
}
