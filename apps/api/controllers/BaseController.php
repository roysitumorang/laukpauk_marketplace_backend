<?php

namespace Application\Api\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;

abstract class BaseController extends Controller {
	protected $_response = [
		'status'  => -1,
		'message' => null,
		'data'    => [],
	];
}