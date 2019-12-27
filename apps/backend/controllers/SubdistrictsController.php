<?php

namespace Application\Backend\Controllers;

use Application\Models\{City, Subdistrict};
use Phalcon\Paginator\Adapter\QueryBuilder;

class SubdistrictsController extends ControllerBase {
	private $_city;

	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		if (!($city_id = $this->dispatcher->getParam('city_id', 'int')) || !($this->_city = City::findFirstById($city_id))) {
			$this->flashSession->error('Kabupaten / kota tidak ditemukan!');
			$this->response->redirect('/admin/provinces');
			$this->response->sendHeaders();
		}
	}

	function indexAction() {
		$this->_render();
	}

	function createAction() {
		$subdistrict       = new Subdistrict;
		$subdistrict->city = $this->_city;
		if ($this->request->isPost()) {
			$subdistrict->setName($this->request->getPost('name'));
			if ($subdistrict->validation() && $subdistrict->create()) {
				$page = $this->dispatcher->getParam('page', 'int') ?: 1;
				$this->flashSession->success('Penambahan kecamatan berhasil!');
				return $this->response->redirect('/admin/subdistricts/index/city_id:' . $this->_city->id . ($page > 1 ? '/page:' . $page : ''));
			}
			foreach ($subdistrict->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_render($subdistrict);
	}

	function updateAction($id) {
		$subdistrict = $this->_city->getRelated('subdistricts', ['id = ?0', 'bind' => [$id]])->getFirst();
		$page        = $this->dispatcher->getParam('page', 'int') ?: 1;
		if (!$subdistrict) {
			$this->flashSession->error('Kecamatan tidak ditemukan!');
			return $this->response->redirect('/admin/subdistricts/index/city_id:' . $this->_city->id . ($page > 1 ? '/page:' . $page : ''));
		}
		if ($this->request->isPost()) {
			$subdistrict->setName($this->request->getPost('name'));
			if ($subdistrict->validation() && $subdistrict->update()) {
				$this->flashSession->success('Update kecamatan berhasil!');
				return $this->response->redirect('/admin/subdistricts/index/city_id:' . $this->_city->id . ($page > 1 ? '/page:' . $page : ''));
			}
			foreach ($subdistrict->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_render($subdistrict);
	}

	private function _render(Subdistrict $subdistrict = null) {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$builder      = $this->modelsManager->createBuilder()
				->from(Subdistrict::class)
				->where('city_id = :city_id:', ['city_id' => $this->_city->id])
				->orderBy('name');
		$paginator    = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page         = $paginator->paginate();
		$pages        = $this->_setPaginationRange($page);
		$subdistricts = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$subdistricts[] = $item;
		}
		$this->view->menu         = $this->_menu('Options');
		$this->view->active_tab   = 'subdistricts';
		$this->view->city         = $this->_city;
		$this->view->province     = $this->_city->province;
		$this->view->subdistricts = $subdistricts;
		$this->view->page         = $page;
		$this->view->pages        = $pages;
		if ($subdistrict) {
			$this->view->subdistrict = $subdistrict;
		}
	}
}
