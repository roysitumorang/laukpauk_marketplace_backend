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

	$router->add('/api/v2(/([a-z0-9]{32}))?/merchants/terms-conditions', [
		'module'         => 'v2',
		'merchant_token' => 2,
		'controller'     => 'merchants',
		'action'         => 'termsConditions',
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

	$router->add('/api/v3(/([a-z0-9]{32}))?/:controller/:action/:params', [
		'module'         => 'v3',
		'merchant_token' => 2,
		'controller'     => 3,
		'action'         => 4,
		'params'         => 5,
	])->convert('action', function($old_action) {
		$parts      = explode('_', strtolower($old_action));
		$new_action = '';
		foreach ($parts as $i => $part) {
			$new_action .= $i ? ucfirst($part) : $part;
		}
		return $new_action;
	});

	$router->add('/api/v3(/([a-z0-9]{32}))?/:controller/:int/:action', [
		'module'         => 'v3',
		'merchant_token' => 2,
		'controller'     => 3,
		'action'         => 5,
		'params'         => 4,
	])->convert('action', function($old_action) {
		$parts      = explode('_', strtolower($old_action));
		$new_action = '';
		foreach ($parts as $i => $part) {
			$new_action .= $i ? ucfirst($part) : $part;
		}
		return $new_action;
	});

	$router->add('/api/v3(/([a-z0-9]{32}))?/:controller/:int', [
		'module'         => 'v3',
		'merchant_token' => 2,
		'controller'     => 3,
		'action'         => 'show',
		'params'         => 4,
	]);

	$router->add('/api/v3(/([a-z0-9]{32}))?/posts/:params', [
		'module'         => 'v3',
		'merchant_token' => 2,
		'controller'     => 'posts',
		'action'         => 'show',
		'params'         => 3,
	]);

	$router->add('/api/v3(/([a-z0-9]{32}))?/merchants/terms-conditions', [
		'module'         => 'v3',
		'merchant_token' => 2,
		'controller'     => 'merchants',
		'action'         => 'termsConditions',
	]);

	$router->add('/api/v3(/([a-z0-9]{32}))?(/merchants/:int)?(/categories/:int)?/products/index/:params', [
		'module'         => 'v3',
		'merchant_token' => 2,
		'controller'     => 'products',
		'action'         => 'index',
		'merchant_id'    => 4,
		'category_id'    => 6,
		'params'         => 7,
	]);

	$router->add('/api/v3(/([a-z0-9]{32}))?/:controller', [
		'module'         => 'v3',
		'merchant_token' => 2,
		'controller'     => 3,
		'action'         => 'index',
	]);

	$router->add('/api/v3(/([a-z0-9]{32}))?', [
		'module'         => 'v3',
		'merchant_token' => 2,
		'controller'     => 'home',
		'action'         => 'index',
	]);

	$router->add('/admin', [
		'module'     => 'backend',
		'controller' => 'home',
		'action'     => 'index',
	]);

	$router->add('/admin/:controller', [
		'module'     => 'backend',
		'controller' => 1,
		'action'     => 'index',
	]);

	$router->add('/admin/:controller/:action/:params', [
		'module'     => 'backend',
		'controller' => 1,
		'action'     => 2,
		'params'     => 3,
	]);

	$router->add('/admin/:controller/:int/:action', [
		'module'     => 'backend',
		'controller' => 1,
		'action'     => 3,
		'params'     => 2,
	])->convert('action', function($old_action) {
		$parts      = explode('_', strtolower($old_action));
		$new_action = '';
		foreach ($parts as $i => $part) {
			$new_action .= $i ? ucfirst($part) : $part;
		}
		return $new_action;
	});

	$router->add('/admin/:controller/:int', [
		'module'     => 'backend',
		'controller' => 1,
		'action'     => 'show',
		'params'     => 2,
	]);

	$router->add('/admin/users/:int/coverage_areas/:params', [
		'module'     => 'backend',
		'controller' => 'coverage_areas',
		'action'     => 'index',
		'user_id'    => 1,
		'params'     => 2,
	]);

	$router->add('/admin/users/:int/coverage_areas/:action/:params', [
		'module'     => 'backend',
		'controller' => 'coverage_areas',
		'action'     => 2,
		'user_id'    => 1,
		'params'     => 3,
	]);

	$router->add('/admin/users/:int/coverage_areas/:int/:action', [
		'module'     => 'backend',
		'controller' => 'coverage_areas',
		'action'     => 3,
		'user_id'    => 1,
		'params'     => 2,
	]);

	$router->add('/admin/users/:int/products/:params', [
		'module'     => 'backend',
		'controller' => 'user_products',
		'action'     => 'index',
		'user_id'    => 1,
		'params'     => 2,
	]);

	$router->add('/admin/users/:int/products/:action/:params', [
		'module'     => 'backend',
		'controller' => 'user_products',
		'action'     => 2,
		'user_id'    => 1,
		'params'     => 3,
	]);

	$router->add('/admin/users/:int/products/:int/:action', [
		'module'     => 'backend',
		'controller' => 'user_products',
		'action'     => 3,
		'user_id'    => 1,
		'params'     => 2,
	]);

	$router->add('/admin/users/:int/product_categories/:params', [
		'module'     => 'backend',
		'controller' => 'product_categories',
		'action'     => 'index',
		'user_id'    => 1,
		'params'     => 2,
	]);

	$router->add('/admin/users/:int/product_categories/:action/:params', [
		'module'     => 'backend',
		'controller' => 'product_categories',
		'action'     => 2,
		'user_id'    => 1,
		'params'     => 3,
	]);

	$router->add('/admin/users/:int/product_categories/:int/:action', [
		'module'     => 'backend',
		'controller' => 'product_categories',
		'action'     => 3,
		'user_id'    => 1,
		'params'     => 2,
	]);

	$router->notFound([
		'module'     => 'frontend',
		'controller' => 'home',
		'action'     => 'route404',
	]);

	$router->removeExtraSlashes(true);

	return $router;
});
