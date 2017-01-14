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
	'encryption_key'   => 'wWw.L@4ukPauK.1d',
	'firebase_api_key' => 'AAAAjN8YD00:APA91bGhZXW6-8NOsWTG0lPKSaVGpksJrpOJ2ojfsSgPDg4fexpGWaukR0BObnw3ffgy-hFyVsNtNiUcP3D6Tpxo-nayraro7eqHiFy1HHQxsACbDWYt9b844Oief8NGYwEgNEwGj_SB_B7aLyNRhC6GX5fJm16UDg',
]);