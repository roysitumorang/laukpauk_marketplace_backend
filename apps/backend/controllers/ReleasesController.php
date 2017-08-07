<?php

namespace Application\Backend\Controllers;

use Application\Models\Release;
use DateTime;
use IntlDateFormatter;
use Phalcon\Paginator\Adapter\Model;

class ReleasesController extends ControllerBase {
	private $_date_formatter;

	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		$this->view->menu = $this->_menu('Options');
		if ($this->dispatcher->getActionName() != 'index') {
			$this->view->application_types = Release::APPLICATION_TYPES;
			$this->view->user_types        = Release::USER_TYPES;
		} else {
			$this->_date_formatter = new IntlDateFormatter(
				'id_ID',
				IntlDateFormatter::FULL,
				IntlDateFormatter::NONE,
				$this->currentDatetime->getTimezone(),
				IntlDateFormatter::GREGORIAN,
				'EEEE, d MMMM yyyy'
			);
		}
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$paginator    = new Model([
			'data'  => Release::find(['order' => 'version DESC, application_type, user_type']),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page     = $paginator->getPaginate();
		$pages    = $this->_setPaginationRange($page);
		$releases = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('created_at', $this->_date_formatter->format(new DateTime($item->created_at, $this->currentDatetime->getTimezone())));
			$item->writeAttribute('features', nl2br($item->features));
			$releases[] = $item;
		}
		$this->view->releases = $releases;
		$this->view->page     = $page;
		$this->view->pages    = $pages;
	}

	function createAction() {
		$release                   = new Release;
		$release->application_type = Release::APPLICATION_TYPES[0];
		$release->user_type        = Release::USER_TYPES[0];
		if ($this->request->isPost()) {
			$this->_set_model_attributes($release);
			if ($release->validation() && $release->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect('/admin/releases');
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($release->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->release = $release;
	}

	function updateAction($id) {
		$release = Release::findFirst($id);
		if (!$release) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('releases');
		}
		if ($this->request->isPost()) {
			$this->_set_model_attributes($release);
			if ($release->validation() && $release->update()) {
				$this->flashSession->success('Update data berhasil.');
				return $this->response->redirect('/admin/releases');
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($release->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->release = $release;
	}

	private function _set_model_attributes(Release &$release) {
		$release->setVersion($this->request->getPost('version'));
		$release->setApplicationType($this->request->getPost('application_type'));
		$release->setUserType($this->request->getPost('user_type'));
		$release->setFeatures($this->request->getPost('features'));
	}
}