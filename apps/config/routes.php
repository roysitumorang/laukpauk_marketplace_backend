<?php

use Phalcon\Mvc\Router;

$di->set('router', function() {
	$router = new Router();

	$router->setDefaultModule('frontend');

	$router->add('/admin(\/home)?', [
		'module'     => 'backend',
		'controller' => 'home',
		'action'     => 'index',
	]);

	$router->add('/admin/users(\/index)?', [
		'module'     => 'backend',
		'controller' => 'users',
		'action'     => 'index',
	]);

	$router->add('/admin/:controller/:action/:params', [
		'module'     => 'backend',
		'controller' => 1,
		'action'     => 2,
		'params'     => 3,
	]);

	$router->removeExtraSlashes(true);

	return $router;
});