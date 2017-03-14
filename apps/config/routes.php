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

	$router->add('/api/v2(/([a-z0-9]{32}))?/:controller/:action/:params', [
		'module'         => 'v2',
		'merchant_token' => 2,
		'controller'     => 3,
		'action'         => 4,
		'params'         => 5,
	]);

	$router->add('/api/v2(/([a-z0-9]{32}))?/posts/:params', [
		'module'         => 'v2',
		'merchant_token' => 2,
		'controller'     => 'posts',
		'action'         => 'show',
		'params'         => 3,
	]);

	$router->add('/api/v2(/([a-z0-9]{32}))?/merchants/:int', [
		'module'         => 'v2',
		'merchant_token' => 2,
		'controller'     => 'merchants',
		'action'         => 'show',
		'params'         => 3,
	]);

	$router->add('/api/v2(/([a-z0-9]{32}))?/notifications/:int', [
		'module'         => 'v2',
		'merchant_token' => 2,
		'controller'     => 'notifications',
		'action'         => 'show',
		'params'         => 3,
	]);

	$router->add('/api/v2(/([a-z0-9]{32}))?/orders/:int/:action', [
		'module'         => 'v2',
		'merchant_token' => 2,
		'controller'     => 'orders',
		'action'         => 4,
		'params'         => 3,
	]);

	$router->add('/api/v2(/([a-z0-9]{32}))?/orders/:int', [
		'module'         => 'v2',
		'merchant_token' => 2,
		'controller'     => 'orders',
		'action'         => 'show',
		'params'         => 3,
	]);

	$router->add('/api/v2(/([a-z0-9]{32}))?/merchants/:int/categories/:int/products/index/:params', [
		'module'         => 'v2',
		'merchant_token' => 2,
		'controller'     => 'products',
		'action'         => 'index',
		'merchant_id'    => 3,
		'category_id'    => 4,
		'params'         => 5,
	]);

	$router->add('/api/v2(/([a-z0-9]{32}))?/:controller', [
		'module'         => 'v2',
		'merchant_token' => 2,
		'controller'     => 3,
		'action'         => 'index',
	]);

	$router->add('/api/v2(/([a-z0-9]{32}))?', [
		'module'         => 'v2',
		'merchant_token' => 2,
		'controller'     => 'home',
		'action'         => 'index',
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

	$router->add('/admin/users/:int/:action', [
		'module'     => 'backend',
		'controller' => 'users',
		'action'     => 2,
		'params'     => 1,
	]);

	$router->add('/admin/users/:int', [
		'module'     => 'backend',
		'controller' => 'users',
		'action'     => 'show',
		'params'     => 1,
	]);

	$router->add('/admin/users/:int/service_areas/:params', [
		'module'     => 'backend',
		'controller' => 'service_areas',
		'action'     => 'index',
		'user_id'    => 1,
		'params'     => 2,
	]);

	$router->add('/admin/users/:int/service_areas/:action/:params', [
		'module'     => 'backend',
		'controller' => 'service_areas',
		'action'     => 2,
		'user_id'    => 1,
		'params'     => 3,
	]);

	$router->add('/admin/users/:int/service_areas/:int/:action', [
		'module'     => 'backend',
		'controller' => 'service_areas',
		'action'     => 3,
		'user_id'    => 1,
		'params'     => 2,
	]);

	$router->add('/admin/users/:int/store_items/:params', [
		'module'     => 'backend',
		'controller' => 'store_items',
		'action'     => 'index',
		'user_id'    => 1,
		'params'     => 2,
	]);

	$router->add('/admin/users/:int/store_items/:action/:params', [
		'module'     => 'backend',
		'controller' => 'store_items',
		'action'     => 2,
		'user_id'    => 1,
		'params'     => 3,
	]);

	$router->add('/admin/users/:int/store_items/:int/:action', [
		'module'     => 'backend',
		'controller' => 'store_items',
		'action'     => 3,
		'user_id'    => 1,
		'params'     => 2,
	]);

	$router->add('/admin/products/:int/:action', [
		'module'     => 'backend',
		'controller' => 'products',
		'action'     => 2,
		'params'     => 1,
	]);

	$router->add('/admin/products/:int/links', [
		'module'     => 'backend',
		'controller' => 'product_links',
		'action'     => 'index',
		'product_id' => 1,
	]);

	$router->add('/admin/products/:int/links/:action/:params', [
		'module'     => 'backend',
		'controller' => 'product_links',
		'action'     => 2,
		'product_id' => 1,
		'params'     => 3,
	]);

	$router->add('/admin/products/:int/links/:int/:action', [
		'module'     => 'backend',
		'controller' => 'product_links',
		'action'     => 3,
		'product_id' => 1,
		'params'     => 2,
	]);

	$router->add('/admin/orders/:action/:params', [
		'module'     => 'backend',
		'controller' => 'orders',
		'action'     => 1,
		'params'     => 2,
	]);

	$router->add('/admin/orders/:int', [
		'module'     => 'backend',
		'controller' => 'orders',
		'action'     => 'show',
		'params'     => 1,
	]);

	$router->add('/admin/orders/:int/:action', [
		'module'     => 'backend',
		'controller' => 'orders',
		'action'     => 2,
		'params'     => 1,
	]);

	$router->notFound([
		'module'     => 'frontend',
		'controller' => 'home',
		'action'     => 'route404',
	]);

	$router->removeExtraSlashes(true);

	return $router;
});
