<?php

use Application\Api\V3\{Buyer, Merchant};
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Group;

$di->set('router', function() {
	$router     = new Router(false);
	$backend    = new Group(['module' => 'backend']);
	$apiV3Buyer = new Group([
		'module'    => 'v3',
		'namespace' => Buyer::class,
		'prefix'    => '/api/v3/buyer',
	]);
	$apiV3Merchant = new Group([
		'module'    => 'v3',
		'namespace' => Merchant::class,
		'prefix'    => '/api/v3/merchant',
	]);
	$backend->setPrefix('/admin');
	$apiV3Buyer->setPrefix('/api/v3/buyer');
	$apiV3Merchant->setPrefix('/api/v3/merchant');

	$apiV3Buyer->add('/:controller/:action/:params', [
		'controller' => 1,
		'action'     => 2,
		'params'     => 3,
	])->convert('action', function($action) {
		return preg_replace_callback('/\_([a-z])/', function($matches) {
			return strtoupper($matches[1]);
		}, $action);
	});

	$apiV3Buyer->add('/:controller/:int/:action', [
		'controller'     => 1,
		'params'         => 2,
		'action'         => 3,
	])->convert('action', function($action) {
		return preg_replace_callback('/\_([a-z])/', function($matches) {
			return strtoupper($matches[1]);
		}, $action);
	});

	$apiV3Buyer->add('/:controller/:int', [
		'controller' => 1,
		'params'     => 2,
		'action'     => 'show',
	]);

	$apiV3Buyer->add('/posts/:params', [
		'controller' => 'posts',
		'action'     => 'show',
		'params'     => 1,
	]);

	$apiV3Buyer->add('(/merchants/:int)?(/categories/:int)?/products/index/:params', [
		'controller'  => 'products',
		'action'      => 'index',
		'merchant_id' => 2,
		'category_id' => 4,
		'params'      => 5,
	]);

	$apiV3Buyer->add('/:controller', [
		'controller' => 1,
		'action'     => 'index',
	]);

	$apiV3Buyer->add('[/]?', [
		'controller' => 'home',
		'action'     => 'index',
	]);

	$apiV3Merchant->add('/:controller/:action/:params', [
		'controller' => 1,
		'action'     => 2,
		'params'     => 3,
	])->convert('action', function($action) {
		return preg_replace_callback('/\_([a-z])/', function($matches) {
			return strtoupper($matches[1]);
		}, $action);
	});

	$apiV3Merchant->add('/:controller/:int/:action', [
		'controller' => 1,
		'params'     => 2,
		'action'     => 3,
	])->convert('action', function($action) {
		return preg_replace_callback('/\_([a-z])/', function($matches) {
			return strtoupper($matches[1]);
		}, $action);
	});

	$apiV3Merchant->add('/:controller/:int', [
		'controller' => 1,
		'params'     => 2,
		'action'     => 'show',
	]);

	$apiV3Merchant->add('/posts/:params', [
		'controller' => 'posts',
		'action'     => 'show',
		'params'     => 1,
	]);

	$apiV3Merchant->add('/:controller', [
		'controller' => 1,
		'action'     => 'index',
	]);

	$apiV3Merchant->add('[/]?', [
		'controller' => 'home',
		'action'     => 'index',
	]);

	$backend->add('[/]?', [
		'controller' => 'home',
		'action'     => 'index',
	]);

	$backend->add('/:controller', [
		'controller' => 1,
		'action'     => 'index',
	]);

	$backend->add('/:controller/:action/:params', [
		'controller' => 1,
		'action'     => 2,
		'params'     => 3,
	])->convert('action', function($action) {
		return preg_replace_callback('/\_([a-z])/', function($matches) {
			return strtoupper($matches[1]);
		}, $action);
	});

	$backend->add('/:controller/:int/:action', [
		'controller' => 1,
		'params'     => 2,
		'action'     => 3,
	])->convert('action', function($action) {
		return preg_replace_callback('/\_([a-z])/', function($matches) {
			return strtoupper($matches[1]);
		}, $action);
	});

	$backend->add('/:controller/:int', [
		'controller' => 1,
		'params'     => 2,
		'action'     => 'show',
	]);

	$backend->add('/users/:int/coverage_areas/:params', [
		'controller' => 'coverage_areas',
		'action'     => 'index',
		'user_id'    => 1,
		'params'     => 2,
	]);

	$backend->add('/users/:int/coverage_areas/:action/:params', [
		'controller' => 'coverage_areas',
		'user_id'    => 1,
		'action'     => 2,
		'params'     => 3,
	]);

	$backend->add('/users/:int/coverage_areas/:int/:action', [
		'controller' => 'coverage_areas',
		'user_id'    => 1,
		'params'     => 2,
		'action'     => 3,
	]);

	$backend->add('/users/:int/products/:params', [
		'controller' => 'user_products',
		'action'     => 'index',
		'user_id'    => 1,
		'params'     => 2,
	]);

	$backend->add('/users/:int/products/:action/:params', [
		'controller' => 'user_products',
		'user_id'    => 1,
		'action'     => 2,
		'params'     => 3,
	]);

	$backend->add('/users/:int/products/:int/:action', [
		'controller' => 'user_products',
		'user_id'    => 1,
		'params'     => 2,
		'action'     => 3,
	]);

	$backend->add('/users/:int/product_categories/:params', [
		'controller' => 'product_categories',
		'action'     => 'index',
		'user_id'    => 1,
		'params'     => 2,
	]);

	$backend->add('/users/:int/product_categories/:action/:params', [
		'controller' => 'product_categories',
		'user_id'    => 1,
		'action'     => 2,
		'params'     => 3,
	]);

	$backend->add('/users/:int/product_categories/:int/:action', [
		'controller' => 'product_categories',
		'user_id'    => 1,
		'params'     => 2,
		'action'     => 3,
	]);

	$backend->add('/users/:int/sale_packages/:params', [
		'controller' => 'sale_packages',
		'action'     => 'index',
		'user_id'    => 1,
		'params'     => 2,
	]);

	$backend->add('/users/:int/sale_packages/:action/:params', [
		'controller' => 'sale_packages',
		'user_id'    => 1,
		'action'     => 2,
		'params'     => 3,
	]);

	$backend->add('/users/:int/sale_packages/:int/:action', [
		'controller' => 'sale_packages',
		'user_id'    => 1,
		'params'     => 2,
		'action'     => 3,
	])->convert('action', function($action) {
		return preg_replace_callback('/\_([a-z])/', function($matches) {
			return strtoupper($matches[1]);
		}, $action);
	});

	$backend->addPost('/users/:int/sale_packages/:int/products/:action', [
		'controller'      => 'sale_package_products',
		'user_id'         => 1,
		'sale_package_id' => 2,
		'action'          => 3,
	]);

	$backend->addPost('/users/:int/sale_packages/:int/products/:params/delete', [
		'controller'      => 'sale_package_products',
		'action'          => 'delete',
		'user_id'         => 1,
		'sale_package_id' => 2,
		'params'          => 3,
	]);

	$router->mount($backend);
	$router->mount($apiV3Buyer);
	$router->mount($apiV3Merchant);

	$router->notFound([
		'module'     => 'backend',
		'controller' => 'home',
		'action'     => 'route404',
	]);

	$router->removeExtraSlashes(true);

	return $router;
});