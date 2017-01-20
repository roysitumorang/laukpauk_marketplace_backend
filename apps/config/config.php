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
		'max_size' => '2M',
		'path'     => APP_PATH . 'public/assets/image/'
	],
	'encryption_key' => 'wWw.L@4ukPauK.1d',
	'onesignal'      => [
		'app_id'  => '4d5abdc5-c536-4832-a67a-fb247d99c669',
		'api_key' => 'NWFiNWZlNjgtNWFjYi00OGE2LTljOTYtNWVkYjRkZGYyNWVm',
	],
]);