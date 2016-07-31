<?php

use Phalcon\Mvc\Router;

$di->set('router', function() {
	$router = new Router();

	$router->setDefaultModule('frontend');

	$router->add('/admin', [
		'module'     => 'backend',
		'controller' => 'home',
		'action'     => 'index',
	]);

	$router->add('/admin/home', [
		'module'     => 'backend',
		'controller' => 'home',
		'action'     => 'index',
	]);

	$router->add('/admin/home/', [
		'module'     => 'backend',
		'controller' => 'home',
		'action'     => 'index',
	]);

	$router->add('/admin/sessions/:action', [
		'module'     => 'backend',
		'controller' => 'sessions',
		'action'     => 1,
	]);

	return $router;
});