<?php

use Phalcon\Mvc\Router;

$di->set('router', function() {
	$router = new Router(false);

	$router->add('/', [
		'module'     => 'frontend',
		'controller' => 'home',
		'action'     => 'index',
	]);

	$router->add('/:controller', [
		'module'     => 'frontend',
		'controller' => 1,
		'action'     => 'index'
	]);

	$router->add('/:controller/:action/:params', [
		'module'     => 'frontend',
		'controller' => 1,
		'action'     => 2,
		'params'     => 3
	]);

	$router->add('/api/v1', [
		'module'     => 'v1',
		'controller' => 'home',
		'action'     => 'index'
	]);

	$router->add('/api/v1/:controller', [
		'module'     => 'v1',
		'controller' => 1,
		'action'     => 'index'
	]);

	$router->add('/api/v1/:controller/:action/:params', [
		'module'     => 'v1',
		'controller' => 1,
		'action'     => 2,
		'params'     => 3
	]);

	$router->add('/api/v2', [
		'module'     => 'v2',
		'controller' => 'home',
		'action'     => 'index'
	]);

	$router->add('/api/v2/:controller', [
		'module'     => 'v2',
		'controller' => 1,
		'action'     => 'index'
	]);

	$router->add('/api/v2/:controller/:action/:params', [
		'module'     => 'v2',
		'controller' => 1,
		'action'     => 2,
		'params'     => 3
	]);

	$router->add('/api/v2/([a-z0-9]{32})', [
		'module'         => 'v2',
		'merchant_token' => 1,
		'controller'     => 'home',
		'action'         => 'index',
	]);

	$router->add('/api/v2/([a-z0-9]{32})/:controller', [
		'module'         => 'v2',
		'merchant_token' => 1,
		'controller'     => 2,
		'action'         => 'index'
	]);

	$router->add('/api/v2/([a-z0-9]{32})/:controller/:action/:params', [
		'module'         => 'v2',
		'merchant_token' => 1,
		'controller'     => 2,
		'action'         => 3,
		'params'         => 4
	]);

	$router->add('/admin', [
		'module'     => 'backend',
		'controller' => 'home',
		'action'     => 'index'
	]);

	$router->add('/admin/:controller', [
		'module'     => 'backend',
		'controller' => 1,
		'action'     => 'index'
	]);

	$router->add('/admin/:controller/:action/:params', [
		'module'     => 'backend',
		'controller' => 1,
		'action'     => 2,
		'params'     => 3
	]);

	$router->notFound([
		'module'     => 'frontend',
		'controller' => 'home',
		'action'     => 'route404',
	]);

	$router->removeExtraSlashes(true);

	return $router;
});
