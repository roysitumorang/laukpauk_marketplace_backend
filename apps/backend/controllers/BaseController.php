<?php

namespace Application\Backend\Controllers;
use Phalcon\Mvc\Controller;
use Phalcon\Text;

class BaseController extends Controller {
	function initialize() {
		$url = $this->request->getQuery('_url');
		if ($this->session->get('user_id')) {
			$this->view->current_user = $this->currentUser;
			$this->view->unread_messages = $this->currentUser->unread_messages;
		} else if (!Text::startsWith($url, '/admin/sessions')) {
			$this->response->redirect('/admin/sessions/new');
		}
	}

	function notFoundAction() {
		// Send a HTTP 404 response header
		$this->response->setStatusCode(404, 'Not Found');
	}
}