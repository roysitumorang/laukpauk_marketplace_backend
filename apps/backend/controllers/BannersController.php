<?php

namespace Application\Backend\Controllers;

use Application\Models\Banner;
use Application\Models\User;
use Exception;
use Phalcon\Paginator\Adapter\Model;

class BannersController extends ControllerBase {
	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$user_id      = $this->dispatcher->getParam('user_id', 'int');
		$paginator    = new Model([
			'data'  => Banner::find(['user_id ' . ($user_id ? "= {$user_id}" : 'IS NULL'), 'order' => 'id DESC']),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page         = $paginator->getPaginate();
		$pages        = $this->_setPaginationRange($page);
		$banners      = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$banners[] = $item;
		}
		$this->view->menu      = $this->_menu('Content');
		$this->view->page      = $page;
		$this->view->pages     = $pages;
		$this->view->banners   = $banners;
		$this->view->user_id   = $user_id;
		$this->view->merchants = User::find([
			'premium_merchant = 1 AND status = 1',
			'columns' => 'id, company',
			'order'   => 'company'
		]);
	}

	function createAction() {
		$banner = new Banner;
		if ($this->request->isPost()) {
			$this->_set_model_attributes($banner);
			if ($banner->validation() && $banner->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect('/admin/banners' . ($banner->user_id ? "/index/user_id:{$banner->user_id}" : ''));
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($banner->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->menu      = $this->_menu('Content');
		$this->view->banner    = $banner;
		$this->view->merchants = User::find([
			'premium_merchant = 1 AND status = 1',
			'columns' => 'id, company',
			'order'   => 'company'
		]);
	}

	function updateAction($id) {
		if (!$banner = Banner::findFirst($id)) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('/admin/banners');
		}
		if ($this->request->isPost()) {
			$this->_set_model_attributes($banner);
			if ($banner->validation() && $banner->update()) {
				$this->flashSession->success('Update data berhasil.');
				return $this->response->redirect('/admin/banners' . ($banner->user_id ? "/index/user_id:{$banner->user_id}" : ''));
			}
			$this->flashSession->error('Update data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($banner->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->banner = $banner;
		$this->view->menu   = $this->_menu('Content');
	}

	function publishAction($id) {
		if (!$banner = Banner::findFirst(['id = ?0 AND published = 0', 'bind' => [$id]])) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('/admin/banners');
		}
		$banner->update(['published' => 1]);
		return $this->response->redirect('/admin/banners' . ($banner->user_id ? "/index/user_id:{$banner->user_id}" : ''));
	}

	function unpublishAction($id) {
		if (!$banner = Banner::findFirst(['id = ?0 AND published = 1', 'bind' => [$id]])) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('/admin/banners');
		}
		$banner->update(['published' => 0]);
		return $this->response->redirect('/admin/banners' . ($banner->user_id ? "/index/user_id:{$banner->user_id}" : ''));
	}

	function deleteAction($id) {
		try {
			if (!$banner = Banner::findFirst($id)) {
				throw new Exception('Data tidak ditemukan.');
			}
			$banner->delete();
			$this->flashSession->success('Data berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect('/admin/banners' . ($banner && $banner->user_id ? "/index/user_id:{$banner->user_id}" : ''));
		}
	}

	private function _set_model_attributes(Banner &$banner) {
		$user_id = $this->request->getPost('user_id', 'int');
		if (!$banner->id && $user_id && User::findFirst(['premium_merchant = 1 AND status = 1 AND id = ?0', 'bind' => $user_id])) {
			$banner->user_id = $user_id;
		}
		$banner->setPublished($this->request->getPost('published'));
		$banner->setNewFile($_FILES['new_file']);
	}
}