<?php

use Phalcon\Config;

return new Config([
	'application' => [
		'modelsDir'  => APP_PATH . 'apps/models/',
		'pluginsDir' => APP_PATH . 'apps/plugins/',
		'libraryDir' => APP_PATH . 'apps/library/Phalcon/',
		'baseUri'    => '/',
	],
	'database' => [
		'adapter'    => 'Mysql',
		'host'       => 'localhost',
		'username'   => 'root',
		'password'   => 'tR1adpass#',
		'dbname'     => 'sayur',
		'persistent' => true,
		'options'    => [
			PDO::ATTR_EMULATE_PREPARES => false,
			PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
		],
	],
	'timezone' => 'Asia/Jakarta',
	'per_page' => 10,
	'upload'   => [
		'max_size' => 2097152,
		'path'     => APP_PATH . 'public/assets/images/'
	],
]);