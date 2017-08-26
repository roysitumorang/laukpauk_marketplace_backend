<?php

namespace Application\Backend\Controllers;

use Application\Models\NotificationTemplate;
use Phalcon\Paginator\Adapter\Model;

class NotificationTemplatesController extends ControllerBase {
	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		$this->view->menu = $this->_menu('Options');
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$paginator    = new Model([
			'data'  => NotificationTemplate::find(),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page                   = $paginator->getPaginate();
		$pages                  = $this->_setPaginationRange($page);
		$notification_templates = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$notification_templates[] = $item;
		}
		$this->view->notification_templates = $notification_templates;
		$this->view->page     = $page;
		$this->view->pages    = $pages;
	}

	function createAction() {
		$notification_template = new NotificationTemplate;
		if ($this->request->isPost()) {
			$this->_set_model_attributes($notification_template);
			if ($notification_template->validation() && $notification_template->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect('/admin/notification_templates');
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($notification_template->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->notification_template = $notification_template;
	}

	function updateAction($id) {
		$notification_template = NotificationTemplate::findFirst($id);
		if (!$notification_template) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('notification_templates');
		}
		if ($this->request->isPost()) {
			$this->_set_model_attributes($notification_template);
			if ($notification_template->validation() && $notification_template->update()) {
				$this->flashSession->success('Update data berhasil.');
				return $this->response->redirect('/admin/notification_templates');
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($notification_template->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->notification_template = $notification_template;
	}

	private function _set_model_attributes(NotificationTemplate &$notification_template) {
		$notification_template->setName($this->request->getPost('name'));
		$notification_template->setTitle($this->request->getPost('title'));
		$notification_template->setAdminTargetUrl($this->request->getPost('admin_target_url'));
		$notification_template->setMerchantTargetUrl($this->request->getPost('merchant_target_url'));
		$notification_template->setOldMobileTargetUrl($this->request->getPost('old_mobile_target_url'));
		$notification_template->setNewMobileTargetUrl($this->request->getPost('new_mobile_target_url'));
	}
}