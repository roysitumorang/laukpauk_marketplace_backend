<?php

namespace Application\Backend\Controllers;

use Phalcon\Mvc\View;

class HomeController extends ControllerBase {
	function indexAction() {
		$this->view->menu = $this->_menu();
	}

	function route404Action() {
		$this->response->setStatusCode(404, 'Not Found');
		$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
	}
}