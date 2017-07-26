<?php

namespace Application\Backend\Controllers;

use Application\Models\Banner;
use Application\Models\User;
use Exception;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\Model;

class BannersController extends ControllerBase {
	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$user_id      = $this->request->getPost('user_id', 'int') ?: $this->dispatcher->getParam('user_id', 'int');
		$paginator    = new Model([
			'data'  => Banner::find(['user_id ' . ($user_id ? "= {$user_id}" : 'IS NULL'), 'order' => 'id DESC']),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page      = $paginator->getPaginate();
		$pages     = $this->_setPaginationRange($page);
		$banners   = [];
		$merchants = [];
		$result    = $this->db->query('SELECT a.id, a.company, COUNT(b.id) AS total_banners FROM users a LEFT JOIN banners b ON a.id = b.user_id WHERE a.premium_merchant = 1 AND a.status = 1 GROUP BY a.id ORDER BY a.company');
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$banners[] = $item;
		}
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$merchants[] = $row;
		}
		$this->view->menu      = $this->_menu('Content');
		$this->view->page      = $page;
		$this->view->pages     = $pages;
		$this->view->banners   = $banners;
		$this->view->user_id   = $user_id;
		$this->view->merchants = $merchants;
	}

	function createAction() {
		$banner = new Banner;
		if ($this->request->isPost()) {
			$user_id = filter_var($this->request->getPost('user_id'), FILTER_VALIDATE_INT);
			if ($user_id && User::findFirst(['premium_merchant = 1 AND status = 1 AND id = ?0', 'bind' => $user_id])) {
				$banner->user_id = $user_id;
			}
			$banner->setPublished($this->request->getPost('published'));
			$banner->setNewFile($_FILES['new_file']);
			if ($banner->validation() && $banner->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect('/admin/banners' . ($banner->user_id ? "/index/user_id:{$banner->user_id}" : ''));
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($banner->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->banner = $banner;
		$this->dispatcher->forward(['action' => 'index']);
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
}