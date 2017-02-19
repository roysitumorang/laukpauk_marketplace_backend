<?php

namespace Application\Backend\Controllers;

use Application\Models\Province;
use Phalcon\Paginator\Adapter\Model;

class ProvincesController extends ControllerBase {
	function indexAction() {
		$this->_render();
	}

	function createAction() {
		$province = new Province;
		if ($this->request->isPost()) {
			$province->setName($this->request->getPost('name'));
			if ($province->validation() && $province->create()) {
				$page = $this->dispatcher->getParam('page', 'int') ?: 1;
				$this->flashSession->success('Penambahan propinsi berhasil!');
				return $this->response->redirect('/admin/provinces' . ($page > 1 ? '/index/page:' . $page : ''));
			}
			foreach ($province->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_render($province);
	}

	function updateAction($id) {
		$province = Province::findFirstById($id);
		$page     = $this->dispatcher->getParam('page', 'int') ?: 1;
		if (!$province) {
			$this->flashSession->error('Propinsi tidak ditemukan!');
			return $this->response->redirect('/admin/provinces' . ($page > 1 ? '/index/page:' . $page : ''));
		}
		if ($this->request->isPost()) {
			$province->setName($this->request->getPost('name'));
			if ($province->validation() && $province->update()) {
				$this->flashSession->success('Update propinsi berhasil!');
				return $this->response->redirect('/admin/provinces' . ($page > 1 ? '/index/page:' . $page : ''));
			}
			foreach ($province->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_render($province);
	}

	private function _render(Province $province = null) {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$paginator    = new Model([
			'data'  => Province::find(['order' => 'name']),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page      = $paginator->getPaginate();
		$pages     = $this->_setPaginationRange($page);
		$provinces = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$provinces[] = $item;
		}
		$this->view->menu      = $this->_menu('Settings');
		$this->view->provinces = $provinces;
		$this->view->page      = $page;
		$this->view->pages     = $pages;
		if ($province) {
			$this->view->province = $province;
		}
	}
}
