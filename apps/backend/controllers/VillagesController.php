<?php

namespace Application\Backend\Controllers;

use Application\Models\Subdistrict;
use Application\Models\Village;
use Phalcon\Paginator\Adapter\Model;

class VillagesController extends ControllerBase {
	private $_subdistrict;

	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		if (!($subdistrict_id = $this->dispatcher->getParam('subdistrict_id', 'int')) || !($this->_subdistrict = Subdistrict::findFirstById($subdistrict_id))) {
			$this->flashSession->error('Kecamatan tidak ditemukan!');
			$this->response->redirect('/admin/provinces');
			$this->response->sendHeaders();
		}
	}

	function indexAction() {
		$this->_render();
	}

	function createAction() {
		$village              = new Village;
		$village->subdistrict = $this->_subdistrict;
		if ($this->request->isPost()) {
			$village->setName($this->request->getPost('name'));
			if ($village->validation() && $village->create()) {
				$page = $this->dispatcher->getParam('page', 'int') ?: 1;
				$this->flashSession->success('Penambahan kelurahan berhasil!');
				return $this->response->redirect('/admin/villages/index/subdistrict_id:' . $this->_subdistrict->id . ($page > 1 ? '/page:' . $page : ''));
			}
			foreach ($village->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_render($village);
	}

	function updateAction($id) {
		$village = $this->_subdistrict->getRelated('villages', ['id = ?0', 'bind' => [$id]])->getFirst();
		$page    = $this->dispatcher->getParam('page', 'int') ?: 1;
		if (!$village) {
			$this->flashSession->error('Kelurahan tidak ditemukan!');
			return $this->response->redirect('/admin/villages/index/subdistrict_id:' . $this->_subdistrict->id . ($page > 1 ? '/page:' . $page : ''));
		}
		if ($this->request->isPost()) {
			$village->setName($this->request->getPost('name'));
			if ($village->validation() && $village->update()) {
				$this->flashSession->success('Update kelurahan berhasil!');
				return $this->response->redirect('/admin/villages/index/subdistrict_id:' . $this->_subdistrict->id . ($page > 1 ? '/page:' . $page : ''));
			}
			foreach ($village->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_render($village);
	}

	private function _render(Village $village = null) {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$paginator    = new Model([
			'data'  => $this->_subdistrict->getRelated('villages', ['order' => 'name']),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page     = $paginator->getPaginate();
		$pages    = $this->_setPaginationRange($page);
		$villages = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$villages[] = $item;
		}
		$city                    = $this->_subdistrict->city;
		$this->view->menu        = $this->_menu('Options');
		$this->view->active_tab  = 'villages';
		$this->view->subdistrict = $this->_subdistrict;
		$this->view->city        = $city;
		$this->view->province    = $city->province;
		$this->view->villages    = $villages;
		$this->view->page        = $page;
		$this->view->pages       = $pages;
		if ($village) {
			$this->view->village = $village;
		}
	}
}
