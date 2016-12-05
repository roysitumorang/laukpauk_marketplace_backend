<?php

namespace Application\Backend\Controllers;

use Application\Models\Page;
use Application\Models\PageCategory;
use Exception;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class PagesController extends BaseController {
	private $_page_category;
	private $_parent;

	function beforeExecuteRoute() {
		$page_category_id = $this->dispatcher->getParam('page_category_id', 'int');
		$parent_id        = $this->dispatcher->getParam('parent_id', 'int');
		try {
			if (!$page_category_id || !($this->_page_category = PageCategory::findFirst($page_category_id))) {
				throw new Exception('/admin/page_categories');
			}
			if ($parent_id && !($this->_parent = $this->_page_category->getRelated('pages', ['conditions' => "id = {$parent_id}"])->getFirst())) {
				throw new Exception('/admin/pages/index/page_category_id:' . $this->_page_category->id);
			}
		} catch (Exception $e) {
			$this->flashSession->error('Data tidak ditemukan');
			$this->response->redirect($e->getMessage());
			$this->response->sendHeaders();
		}
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$paginator    = new PaginatorModel([
			'data'  => $this->_parent ? $this->_parent->getRelated('sub_pages') : $this->_page_category->getRelated('pages', ['conditions' => 'parent_id IS NULL']),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page         = $paginator->getPaginate();
		$pages        = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('removable', !$item->sub_pages->count());
			if ($item->picture) {
				$item->writeAttribute('thumbnail', $item->getThumbnail(150, 100));
			}
			$pages[] = $item;
		}
		$this->view->menu          = $this->_menu('Content');
		$this->view->page_category = $this->_page_category;
		$this->view->page          = $page;
		$this->view->pages         = $pages;
		if ($this->_parent) {
			$this->view->parent_id = $this->_parent->id;
		}
	}

	function createAction() {
		$page           = new Page;
		$page->category = $this->_page_category;
		$page->position = 0;
		if ($this->_parent) {
			$page->parent = $this->_parent;
		}
		if ($this->request->isPost()) {
			$this->_set_model_attributes($page);
			if ($page->validation() && $page->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect("/admin/pages/index/page_category_id:{$this->_page_category->id}" . ($page->parent ? '/parent_id:' . $page->parent->id : ''));
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($page->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->page_category = $this->_page_category;
		$this->view->page          = $page;
		$this->view->url_targets   = Page::URL_TARGETS;
		$this->view->menu          = $this->_menu('Content');
		if ($this->_parent) {
			$this->view->parent_id = $this->_parent->id;
		}
	}

	function updateAction($id) {
		if (!filter_var($id, FILTER_VALIDATE_INT) || !($page = $this->_page_category->getRelated('pages', ['conditions' => "id = {$id}"])->getFirst())) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('/admin/pages/index/page_category_id:' . $this->_page_category->id . ($this->_parent ? '/parent_id:' . $this->_parent->id : ''));
		}
		if ($page->picture) {
			$page->thumbnail = $page->getThumbnail(150, 100);
		}
		if ($this->request->isPost()) {
			if ($this->dispatcher->hasParam('published')) {
				$page->update(['published' => $page->published ? 0 : 1]);
				return $this->response->redirect("/admin/pages/index/page_category_id:{$this->_page_category->id}" . ($page->parent ? '/parent_id:' . $page->parent->id : ''));
			} else if ($this->dispatcher->hasParam('delete_picture')) {
				$page->deletePicture();
				return $this->response->redirect("/admin/pages/index/page_category_id:{$this->_page_category->id}" . ($page->parent ? '/parent_id:' . $page->parent->id : ''));
			}
			$this->_set_model_attributes($page);
			if ($page->validation() && $page->update()) {
				$this->flashSession->success('Update data berhasil.');
				return $this->response->redirect("/admin/pages/index/page_category_id:{$this->_page_category->id}" . ($page->parent ? '/parent_id:' . $page->parent->id : ''));
			}
			$this->flashSession->error('Update data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($page->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->page_category = $this->_page_category;
		$this->view->page          = $page;
		$this->view->url_targets   = Page::URL_TARGETS;
		$this->view->menu          = $this->_menu('Content');
	}

	function deleteAction($id) {
		try {
			if (!filter_var($id, FILTER_VALIDATE_INT) || !($page = $this->_page_category->getRelated('pages', ['conditions' => "id = {$id}"])->getFirst()) || $page->sub_pages->count()) {
				throw new Exception('Data tidak ditemukan.');
			}
			$page->delete();
			$this->flashSession->success('Data berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect("/admin/pages/index/page_category_id:{$this->_page_category->id}" . ($page->parent ? '/parent_id:' . $page->parent->id : ''));
		}
	}

	private function _set_model_attributes(&$page) {
		$page->setName($this->request->getPost('name'));
		$page->setNewPermalink($this->request->getPost('new_permalink'));
		$page->setUrl($this->request->getPost('url'));
		$page->setBody($this->request->getPost('body'));
		$page->setUrlTarget($this->request->getPost('url_target'));
		$page->setMetaTitle($this->request->getPost('meta_title'));
		$page->setMetaDesc($this->request->getPost('meta_desc'));
		$page->setMetaKeyword($this->request->getPost('meta_keyword'));
		$page->setPublished($this->request->getPost('published'));
		$page->setPosition($this->request->getPost('position'));
		$page->setNewPicture($_FILES['picture']);
	}
}