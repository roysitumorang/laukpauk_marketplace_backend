<?php

use Phalcon\Logger;
use Phalcon\Logger\Adapter\File;
use Phalcon\Assets\Manager as AssetsManager;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Db\Adapter\Pdo\Postgresql;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use Phalcon\Mvc\Model\Metadata\Memory;
use Phalcon\Mvc\Url;
use Phalcon\Flash\Direct;
use Phalcon\Flash\Session;
use Phalcon\Http\Request;

/**
 * Application logger
 */
$di->set('logger', function() {
	$logger = new File(APP_PATH . 'apps/logs/' . date('Y-m-d') . '.log');
	$logger->setLogLevel(Logger::CRITICAL);
	return $logger;
});

$di->set('config', function() use($config) {
	return $config;
});

/**
 * A component that allows manage static resources such as css stylesheets or javascript libraries in a web application
 */
$di->set('assets', function() {
	return new AssetsManager;
}, true);

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->set('url', function() {
	$url = new Url;
	$url->setBaseUri($this->getConfig()->application->baseUri);
	return $url;
}, true);

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('db', function() {
	$database = $this->getConfig()->database;
	$params   = [
		'host'       => $database->host,
		'dbname'     => $database->dbname,
		'username'   => $database->username,
		'password'   => $database->password,
		'persistent' => $database->persistent,
	];
	if ($database->adapter == 'Mysql') {
		return new Mysql($params);
	}
	return new Postgresql($params);
});

$di->set('transactionManager', function() {
	return new TransactionManager;
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->set('modelsMetadata', function() {
	return new Memory;
});

/**
 * Register the flash service with custom CSS classes
 */
$di->set('flash', function() {
	return new Direct([
		'error'   => 'alert alert-danger',
		'success' => 'alert alert-success',
		'notice'  => 'alert alert-info',
		'warning' => 'alert alert-warning',
	]);
});

$di->set('flashSession', function() {
	return new Session([
		'error'   => 'alert alert-danger',
		'success' => 'alert alert-success',
		'notice'  => 'alert alert-info',
		'warning' => 'alert alert-warning',
	]);
});

$di->set('request', function() {
	return new Request;
});

$di->set('currentDatetime', function() {
	$current_datetime = DateTimeImmutable::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
	return $current_datetime->setTimezone(new DateTimeZone($this->getConfig()->timezone));
});