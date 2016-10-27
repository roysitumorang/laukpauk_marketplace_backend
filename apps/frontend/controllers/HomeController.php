<?php

namespace Application\Frontend\Controllers;
use Phalcon\Mvc\Controller;

class HomeController extends Controller {
	function route404Action() {
		$this->response->setStatusCode(404, 'Not Found');
		$this->view->pick(['_layouts/error-404']);
		$this->response->send();
	}
}