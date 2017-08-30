<?php

namespace Application\Backend\Controllers;

use Application\Models\Banner;
use Ds\Set;
use Exception;
use Phalcon\Paginator\Adapter\Model;

class BannersController extends ControllerBase {
	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$paginator    = new Model([
			'data'  => Banner::find(['order' => 'id DESC']),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page    = $paginator->getPaginate();
		$pages   = $this->_setPaginationRange($page);
		$banners = new Set;
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$banners->add($item);
		}
		$this->view->menu    = $this->_menu('Content');
		$this->view->page    = $page;
		$this->view->pages   = $pages;
		$this->view->banners = $banners;
	}

	function createAction() {
		$banner = new Banner;
		if ($this->request->isPost()) {
			$banner->setPublished($this->request->getPost('published'));
			$banner->setNewFile($_FILES['new_file']);
			if ($banner->validation() && $banner->create()) {
				$this->flashSession->success('Penambahan banner berhasil.');
				return $this->response->redirect('/admin/banners');
			}
			$this->flashSession->error('Penambahan banner tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($banner->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->banner = $banner;
		$this->dispatcher->forward(['action' => 'index']);
	}

	function toggleStatusAction($id) {
		if (!$banner = Banner::findFirst($id)) {
			$this->flashSession->error('Banner tidak ditemukan.');
			return $this->dispatcher->forward('/admin/banners');
		}
		$banner->update(['published' => $banner->published ? 0 : 1]);
		return $this->response->redirect('/admin/banners');
	}

	function deleteAction($id) {
		try {
			if (!$banner = Banner::findFirst($id)) {
				throw new Exception('Banner tidak ditemukan.');
			}
			$banner->delete();
			$this->flashSession->success('Banner berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect('/admin/banners');
		}
	}
}