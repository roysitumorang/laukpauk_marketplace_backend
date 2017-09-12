<?php

use Exception;
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

$di->set('jsonWebToken', function() use($di) {
	return new class($di) {
		private $_timestamp, $secret_key;

		function __construct($di) {
			$this->_timestamp  = $di->getCurrentDatetime()->format('U');
			$this->_secret_key = $di->getConfig()->encryption_key;
		}

		function encode(array $data) {
			$header  = $this->_base64url_encode('{"alg":"HS256","typ":"JWT"}');
			$payload = $this->_base64url_encode(json_encode([
				'iat'  => $this->_timestamp,
				'nbf'  => $this->_timestamp,
				'exp'  => $this->_timestamp + 1209600,
				'data' => $data,
			], JSON_NUMERIC_CHECK));
			$header_payload = $header . '.' . $payload;
			$signature      = $this->_sign($header_payload);
			return $header_payload . '.' . $this->_base64url_encode($signature);
		}

		function decode($token) {
			$segments = explode('.', $token);
			if (count($segments) != 3) {
				throw new Exception('Wrong number of segments');
			}
			if (!($header = json_decode($this->_base64url_decode($segments[0])))) {
				throw new Exception('Invalid header encoding');
			}
			if (!($payload = json_decode($this->_base64url_decode($segments[1])))) {
				throw new Exception('Invalid claims encoding');
			}
			if (!($signature = $this->_base64url_decode($segments[2]))) {
				throw new Exception('Invalid signature encoding');
			}
			if (empty($header->alg)) {
				throw new Exception('Empty algorithm');
			}
			if ($header->alg != 'HS256') {
				throw new Exception('Invalid algorithm');
			}
			if (!hash_equals($signature, $this->_sign($segments[0] . '.' . $segments[1]))) {
				throw new Exception('Signature verification failed');
			}
			if (isset($payload->nbf) && $payload->nbf > $this->_timestamp) {
				throw new Exception('Cannot handle token prior to ' . date(DateTime::ISO8601, $payload->nbf));
			}
			if (isset($payload->iat) && $payload->iat > $this->_timestamp) {
				throw new Exception('Cannot handle token prior to ' . date(DateTime::ISO8601, $payload->iat));
			}
			if (isset($payload->exp) && $this->_timestamp >= $payload->exp) {
				throw new Exception('Expired token');
			}
			if (!is_object($payload->data)) {
				throw new Exception('Invalid payload');
			}
			return $payload->data;
		}

		private function _sign($message) {
			return hash_hmac('sha256', $message, $this->_secret_key, true);
		}

		private function _base64url_decode($input) {
			return base64_decode(str_pad(strtr($input, '-_', '+/'), strlen($input) % 4, '=', STR_PAD_RIGHT));
		}

		private function _base64url_encode($input) {
			return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
		}
	};
});