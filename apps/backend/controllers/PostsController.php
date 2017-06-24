<?php

namespace Application\Backend\Controllers;

use Application\Models\Post;
use Application\Models\PostCategory;
use Exception;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class PostsController extends ControllerBase {
	private $_post_category;

	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		if (!($post_category_id = $this->dispatcher->getParam('post_category_id', 'int')) || !($this->_post_category = PostCategory::findFirst($post_category_id))) {
			$this->flashSession->error('Data tidak ditemukan');
			$this->response->redirect('/admin/post_categories');
			$this->response->sendHeaders();
		}
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$paginator    = new PaginatorModel([
			'data'  => $this->_post_category->getRelated('posts', ['order' => 'id DESC']),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page         = $paginator->getPaginate();
		$pages        = $this->_setPaginationRange($page);
		$posts        = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('removable', !$item->comments->count());
			if ($item->picture) {
				$item->writeAttribute('thumbnail', $item->getThumbnail(500, 300));
			}
			$posts[] = $item;
		}
		$this->view->menu          = $this->_menu('Content');
		$this->view->post_category = $this->_post_category;
		$this->view->page          = $page;
		$this->view->pages         = $pages;
		$this->view->posts         = $posts;
		$this->view->fqdn          = 'http' . ($this->request->getServer('SERVER_PORT') == 443 ? 's' : '') . '://' . $this->request->getServer('HTTP_HOST');
	}

	function createAction() {
		$post           = new Post;
		$post->category = $this->_post_category;
		if ($this->request->isPost()) {
			$this->_set_model_attributes($post);
			if ($post->validation() && $post->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect("/admin/posts/index/post_category_id:{$this->_post_category->id}");
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($post->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->post_category = $this->_post_category;
		$this->view->post          = $post;
		$this->view->menu          = $this->_menu('Content');
	}

	function updateAction($id) {
		if (!filter_var($id, FILTER_VALIDATE_INT) || !($post = $this->_post_category->getRelated('posts', ['conditions' => "id = {$id}"])->getFirst())) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward("/admin/posts/index/post_category_id:{$this->_post_category->id}");
		}
		if ($post->picture) {
			$post->thumbnail = $post->getThumbnail(150, 100);
		}
		if ($this->request->isPost()) {
			if ($this->dispatcher->hasParam('published')) {
				$post->update(['published' => $post->published ? 0 : 1]);
				return $this->response->redirect("/admin/posts/index/post_category_id:{$this->_post_category->id}");
			} else if ($this->dispatcher->hasParam('delete_picture')) {
				$post->deletePicture();
				return $this->response->redirect("/admin/posts/update/{$post->id}/post_category_id:{$this->_post_category->id}");
			}
			$this->_set_model_attributes($post);
			if ($post->validation() && $post->update()) {
				$this->flashSession->success('Update data berhasil.');
				return $this->response->redirect("/admin/posts/index/post_category_id:{$this->_post_category->id}");
			}
			$this->flashSession->error('Update data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($post->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->post_category = $this->_post_category;
		$this->view->post          = $post;
		$this->view->menu          = $this->_menu('Content');
	}

	function deleteAction($id) {
		try {
			if (!filter_var($id, FILTER_VALIDATE_INT) || !($post = $this->_post_category->getRelated('posts', ['conditions' => "id = {$id}"])->getFirst()) || $post->comments->count()) {
				throw new Exception('Data tidak ditemukan.');
			}
			$post->delete();
			$this->flashSession->success('Data berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect("/admin/posts/index/post_category_id:{$this->_post_category->id}");
		}
	}

	private function _set_model_attributes(Post &$post) {
		$post->setSubject($this->request->getPost('subject'));
		$post->setNewPermalink($this->request->getPost('new_permalink'));
		$post->setCustomLink($this->request->getPost('custom_link'));
		$post->setBody($this->request->getPost('body'));
		$post->setMetaTitle($this->request->getPost('meta_title'));
		$post->setMetaKeyword($this->request->getPost('meta_keyword'));
		$post->setMetaDesc($this->request->getPost('meta_desc'));
		$post->setPublished($this->request->getPost('published'));
		$post->setNewPicture($_FILES['picture']);
	}
}