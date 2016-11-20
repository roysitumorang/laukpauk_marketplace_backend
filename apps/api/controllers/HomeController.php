<?php

namespace Application\Api\Controllers;

use Phalcon\Mvc\Controller;

class HomeController extends Controller {
	function route404Action() {
		$this->_response['message'] = 'Halaman tidak ditemukan';
		$this->response->setStatusCode(404, 'Not Found');
		$this->response->setJsonContent($this->_response);
		return $this->response;
	}
}