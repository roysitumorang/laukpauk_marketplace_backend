<?php

namespace Application\Backend\Controllers;

use Application\Models\Banner;
use Application\Models\BannerCategory;
use Exception;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class BannersController extends ControllerBase {
	private $_banner_category;

	function beforeExecuteRoute() {
		if (!($banner_category_id = $this->dispatcher->getParam('banner_category_id', 'int')) || !($this->_banner_category = BannerCategory::findFirst($banner_category_id))) {
			$this->flashSession->error('Data tidak ditemukan');
			$this->response->redirect('/admin/banner_categories');
			$this->response->sendHeaders();
		}
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$paginator    = new PaginatorModel([
			'data'  => $this->_banner_category->getRelated('banners', ['order' => 'id DESC']),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page         = $paginator->getPaginate();
		$pages        = $this->_setPaginationRange($page);
		$banners      = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			if ($item->file_name) {
				$item->writeAttribute('thumbnail', $item->getThumbnail(800, 400));
			}
			$banners[] = $item;
		}
		$this->view->menu            = $this->_menu('Content');
		$this->view->banner_category = $this->_banner_category;
		$this->view->page            = $page;
		$this->view->pages           = $pages;
		$this->view->banners         = $banners;
	}

	function createAction() {
		$banner           = new Banner;
		$banner->category = $this->_banner_category;
		if ($this->request->isPost()) {
			$this->_set_model_attributes($banner);
			if ($banner->validation() && $banner->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect("/admin/banners/index/banner_category_id:{$this->_banner_category->id}");
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($banner->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->banner_category = $this->_banner_category;
		$this->view->banner          = $banner;
		$this->view->banner_types    = Banner::TYPES;
		$this->view->menu            = $this->_menu('Content');
	}

	function updateAction($id) {
		if (!filter_var($id, FILTER_VALIDATE_INT) || !($banner = $this->_banner_category->getRelated('banners', ['conditions' => "id = {$id}"])->getFirst())) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward("/admin/banners/index/banner_category_id:{$this->_banner_category->id}");
		}
		if ($banner->file_name) {
			$banner->thumbnail = $banner->getThumbnail(150, 100);
		}
		if ($this->request->isPost()) {
			if ($this->dispatcher->hasParam('published')) {
				$banner->update(['published' => $banner->published ? 0 : 1]);
				return $this->response->redirect("/admin/banners/index/banner_category_id:{$this->_banner_category->id}");
			} else if ($this->dispatcher->hasParam('delete_picture')) {
				$banner->deletePicture();
				return $this->response->redirect("/admin/banners/update/{$banner->id}/banner_category_id:{$this->_banner_category->id}");
			}
			$this->_set_model_attributes($banner);
			if ($banner->validation() && $banner->update()) {
				$this->flashSession->success('Update data berhasil.');
				return $this->response->redirect("/admin/banners/index/banner_category_id:{$this->_banner_category->id}");
			}
			$this->flashSession->error('Update data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($banner->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->banner_category = $this->_banner_category;
		$this->view->banner          = $banner;
		$this->view->banner_types    = Banner::TYPES;
		$this->view->menu            = $this->_menu('Content');
	}

	function deleteAction($id) {
		try {
			if (!filter_var($id, FILTER_VALIDATE_INT) || !($banner = $this->_banner_category->getRelated('banners', ['conditions' => "id = {$id}"])->getFirst())) {
				throw new Exception('Data tidak ditemukan.');
			}
			$banner->delete();
			$this->flashSession->success('Data berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect("/admin/banners/index/banner_category_id:{$this->_banner_category->id}");
		}
	}

	private function _set_model_attributes(Banner &$banner) {
		$banner->setName($this->request->getPost('name'));
		$banner->setLink($this->request->getPost('link'));
		$banner->setType($this->request->getPost('type'));
		$banner->setPublished($this->request->getPost('published'));
		$banner->setFileUrl($this->request->getPost('file_url'));
		$banner->setNewFile($_FILES['new_file']);
	}
}