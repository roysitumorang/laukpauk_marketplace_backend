<?php

namespace Application\Backend\Controllers;

use Application\Models\Banner;
use Phalcon\Paginator\Adapter\Model;

class BannersController extends ControllerBase {
	function indexAction() {
		$banners      = [];
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int', 1);
		$offset       = ($current_page - 1) * $limit;
		$pagination   = (new Model([
			'data'  => Banner::find(['order' => 'id DESC']),
			'limit' => $limit,
			'page'  => $current_page,
		]))->paginate();
		foreach ($pagination->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$banners[] = $item;
		}
		$this->view->menu       = $this->_menu('Content');
		$this->view->pagination = $pagination;
		$this->view->pages      = $this->_setPaginationRange($pagination);
		$this->view->banners    = $banners;
	}

	function createAction() {
		$banner = new Banner;
		if ($this->request->isPost()) {
			$banner->assign($this->request->getPost(), null, ['published']);
			if ($this->request->hasFiles()) {
				$banner->setNewFile(current(array_filter($this->request->getUploadedFiles(), function(&$v, $k) {
					return $v->getKey() == 'new_file';
				}, ARRAY_FILTER_USE_BOTH)));
			}
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
		if ($this->request->isPost()) {
			if (!$banner = Banner::findFirst($id)) {
				$this->flashSession->error('Banner tidak ditemukan.');
			} else {
				$banner->update(['published' => $banner->published ? 0 : 1]);
			}
		}
		return $this->response->redirect('/admin/banners');
	}

	function deleteAction($id) {
		if ($this->request->isPost()) {
			if (!$banner = Banner::findFirst($id)) {
				$this->flashSession->error('Banner tidak ditemukan.');
			} else {
				$banner->delete();
				$this->flashSession->success('Banner berhasil dihapus');
			}
		}
		return $this->response->redirect('/admin/banners');
	}
}