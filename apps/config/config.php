<?php

use Phalcon\Config;

return (new Config)->merge([
	'application' => [
		'modelsDir'  => APP_PATH . 'apps/models/',
		'pluginsDir' => APP_PATH . 'apps/plugins/',
		'baseUri'    => '/',
	],
	'database' => [
		'host'       => 'localhost',
		'username'   => 'postgres',
		'password'   => 'tR1adpass#',
		'dbname'     => 'laukpauk_marketplace',
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
	'push_api_key'   => 'AAAAjN8YD00:APA91bGhZXW6-8NOsWTG0lPKSaVGpksJrpOJ2ojfsSgPDg4fexpGWaukR0BObnw3ffgy-hFyVsNtNiUcP3D6Tpxo-nayraro7eqHiFy1HHQxsACbDWYt9b844Oief8NGYwEgNEwGj_SB_B7aLyNRhC6GX5fJm16UDg',
	'onesignal'      => [
		'app_id'  => '4d5abdc5-c536-4832-a67a-fb247d99c669',
		'api_key' => 'NWFiNWZlNjgtNWFjYi00OGE2LTljOTYtNWVkYjRkZGYyNWVm',
	],
	'sms' => [
		'send_endpoint'    => 'https://reguler.zenziva.net/apps/smsapi.php',
		'balance_endpoint' => 'https://reguler.zenziva.net/apps/smsapibalance.php',
		'username'         => '3cg4o9',
		'password'         => 'l4ukp@uk',
	],
]);
