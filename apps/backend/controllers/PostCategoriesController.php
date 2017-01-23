<?php

namespace Application\Backend\Controllers;

use Application\Models\PostCategory;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class PostCategoriesController extends ControllerBase {
	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$paginator    = new PaginatorModel([
			'data'  => PostCategory::find(['order' => 'name']),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page            = $paginator->getPaginate();
		$pages           = $this->_setPaginationRange($page);
		$post_categories = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('removable', !$item->posts->count());
			$post_categories[] = $item;
		}
		$this->view->menu            = $this->_menu('Content');
		$this->view->post_categories = $post_categories;
		$this->view->page            = $page;
		$this->view->pages           = $pages;
	}

	function createAction() {
		$post_category            = new PostCategory;
		$post_category->published = 0;
		if ($this->request->isPost()) {
			$this->_set_model_attributes($post_category);
			if ($post_category->validation() && $post_category->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect('/admin/post_categories');
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($post_category->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->menu          = $this->_menu('Content');
		$this->view->post_category = $post_category;
	}

	function updateAction($id) {
		if (!filter_var($id, FILTER_VALIDATE_INT) || !($post_category = PostCategory::findFirst($id))) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('post_categories');
		}
		if ($this->request->isPost()) {
			if ($this->dispatcher->getParam('published')) {
				$post_category->update(['published' => $post_category->published ? 0 : 1]);
				return $this->response->redirect('/admin/post_categories');
			}
			$this->_set_model_attributes($post_category);
			if ($post_category->validation() && $post_category->update()) {
				$this->flashSession->success('Update data berhasil.');
				return $this->response->redirect('/admin/post_categories');
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($post_category->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->menu          = $this->_menu('Content');
		$this->view->post_category = $post_category;
	}

	function deleteAction($id) {
		try {
			if (!filter_var($id, FILTER_VALIDATE_INT) || !($post_category = PostCategory::findFirst($id)) || $post_category->posts->count()) {
				throw new Exception('Data tidak ditemukan.');
			}
			$post_category->delete();
			$this->flashSession->success('Data berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect('/admin/post_categories');
		}
	}

	private function _set_model_attributes(PostCategory &$post_category) {
		$post_category->setName($this->request->getPost('name'));
		$post_category->setNewPermalink($this->request->getPost('new_permalink'));
		$post_category->setAllowComments($this->request->getPost('allow_comments'));
		$post_category->setCommentModeration($this->request->getPost('comment_moderation'));
		$post_category->setPublished($this->request->getPost('published'));
	}
}