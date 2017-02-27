<?php

namespace Application\Api\V2\Controllers;

use Phalcon\Mvc\Controller;

class ErrorsController extends Controller {
	function notFoundAction() {
		$this->_response['message'] = 'Halaman tidak ditemukan';
		$this->response->setStatusCode(404, 'Not Found');
		$this->response->setJsonContent($this->_response);
		return $this->response;
	}

	function uncaughtException() {
		$this->_response['message'] = 'Internal server error';
		$this->response->setStatusCode(500, 'Internal Server Error');
		$this->response->setJsonContent($this->_response);
		return $this->response;
	}
}