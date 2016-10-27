<?php

namespace Application\Backend\Controllers;

class HomeController extends BaseController {
	function indexAction() {
		$this->view->menu = $this->_menu();
	}

	function route404Action() {
		$this->response->setStatusCode(404, 'Not Found');
		$this->view->pick(['_layouts/error-404']);
		$this->response->send();
	}
}