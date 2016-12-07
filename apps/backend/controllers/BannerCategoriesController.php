<?php

namespace Application\Backend\Controllers;

use Application\Models\BannerCategory;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class BannerCategoriesController extends ControllerBase {
	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$paginator    = new PaginatorModel([
			'data'  => BannerCategory::find(['order' => 'name']),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page              = $paginator->getPaginate();
		$pages             = $this->_setPaginationRange($page);
		$banner_categories = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('removable', !$item->banners->count());
			$banner_categories[] = $item;
		}
		$this->view->menu              = $this->_menu('Content');
		$this->view->banner_categories = $banner_categories;
		$this->view->page              = $page;
		$this->view->pages             = $pages;
	}

	function createAction() {
		$banner_category            = new BannerCategory;
		$banner_category->published = 0;
		if ($this->request->isPost()) {
			$this->_set_model_attributes($banner_category);
			if ($banner_category->validation() && $banner_category->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect('/admin/banner_categories');
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($banner_category->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->menu            = $this->_menu('Content');
		$this->view->banner_category = $banner_category;
	}

	function updateAction($id) {
		if (!filter_var($id, FILTER_VALIDATE_INT) || !($banner_category = BannerCategory::findFirst($id))) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('banner_categories');
		}
		if ($this->request->isPost()) {
			$this->_set_model_attributes($banner_category);
			if ($banner_category->validation() && $banner_category->update()) {
				$this->flashSession->success('Update data berhasil.');
				return $this->response->redirect('/admin/banner_categories');
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($banner_category->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->menu            = $this->_menu('Content');
		$this->view->banner_category = $banner_category;
	}

	function deleteAction($id) {
		try {
			if (!filter_var($id, FILTER_VALIDATE_INT) || !($banner_category = BannerCategory::findFirst($id)) || $banner_category->banners->count()) {
				throw new Exception('Data tidak ditemukan.');
			}
			$banner_category->delete();
			$this->flashSession->success('Data berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect('/admin/banner_categories');
		}
	}

	private function _set_model_attributes(BannerCategory &$banner_category) {
		$banner_category->setName($this->request->getPost('name'));
	}
}