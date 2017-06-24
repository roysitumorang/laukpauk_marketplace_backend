<?php

namespace Application\Backend\Controllers;

use Application\Models\City;
use Application\Models\Province;
use Phalcon\Paginator\Adapter\Model;

class CitiesController extends ControllerBase {
	private $_province;

	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		if (!($province_id = $this->dispatcher->getParam('province_id', 'int')) || !($this->_province = Province::findFirstById($province_id))) {
			$this->flashSession->error('Propinsi tidak ditemukan!');
			$this->response->redirect('/admin/provinces');
			$this->response->sendHeaders();
		}
	}

	function indexAction() {
		$this->_render();
	}

	function createAction() {
		$city           = new City;
		$city->province = $this->_province;
		if ($this->request->isPost()) {
			$city->setType($this->request->getPost('type'));
			$city->setName($this->request->getPost('name'));
			if ($city->validation() && $city->create()) {
				$page = $this->dispatcher->getParam('page', 'int') ?: 1;
				$this->flashSession->success('Penambahan kabupaten / kota berhasil!');
				return $this->response->redirect('/admin/cities/index/province_id:' . $this->_province->id . ($page > 1 ? '/page:' . $page : ''));
			}
			foreach ($city->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_render($city);
	}

	function updateAction($id) {
		$city = $this->_province->getRelated('cities', ['id = ?0', 'bind' => [$id]])->getFirst();
		$page = $this->dispatcher->getParam('page', 'int') ?: 1;
		if (!$city) {
			$this->flashSession->error('Kabupaten / kota tidak ditemukan!');
			return $this->response->redirect('/admin/cities/index/province_id:' . $this->_province->id . ($page > 1 ? '/page:' . $page : ''));
		}
		if ($this->request->isPost()) {
			$city->setType($this->request->getPost('type'));
			$city->setName($this->request->getPost('name'));
			if ($city->validation() && $city->update()) {
				$this->flashSession->success('Update kabupaten / kota berhasil!');
				return $this->response->redirect('/admin/cities/index/province_id:' . $this->_province->id . ($page > 1 ? '/page:' . $page : ''));
			}
			foreach ($city->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_render($city);
	}

	private function _render(City $city = null) {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$paginator    = new Model([
			'data'  => $this->_province->getRelated('cities', ['order' => 'type, name']),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page   = $paginator->getPaginate();
		$pages  = $this->_setPaginationRange($page);
		$cities = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$cities[] = $item;
		}
		$this->view->menu       = $this->_menu('Options');
		$this->view->active_tab = 'cities';
		$this->view->province   = $this->_province;
		$this->view->cities     = $cities;
		$this->view->page       = $page;
		$this->view->pages      = $pages;
		$this->view->types      = City::TYPES;
		if ($city) {
			$this->view->city = $city;
		}
	}
}
