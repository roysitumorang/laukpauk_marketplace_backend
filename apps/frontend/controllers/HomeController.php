<?php

namespace Application\Frontend\Controllers;

use Phalcon\Mvc\Controller;

class HomeController extends Controller {
	private $_response;

	function beforeExecuteRoute() {
		$this->_response = ['status' => -1];
	}

	function indexAction() {
		$this->_response['status']  = 1;
		$this->_response['message'] = "What's up Dude? :)";
	}

	function notFoundAction() {
		$this->response->setStatusCode(404, 'Not Found');
		$this->_response['message'] = 'Page Not Found';
	}

	function uncaughtExceptionAction() {
		$this->response->setStatusCode(500, 'Internal Server Error');
		$this->_response['message'] = 'Internal Server Error';
	}

	function afterExecuteRoute() {
		$this->response->setJsonContent($this->_response);
		exit($this->response->send());
	}
}