<?php

namespace Application\Backend\Controllers;

use Application\Models\Post;
use Application\Models\PostCategory;
use Application\Models\Role;
use Application\Models\User;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\QueryBuilder;

class PostsController extends ControllerBase {
	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$user_id      = $this->request->getPost('user_id', 'int') ?: $this->dispatcher->getParam('user_id', 'int');
		if ($user_id && !User::findFirst(['status = 1 AND role_id = ?0 AND premium_merchant = 1 AND id = ?1', 'bind' => [Role::MERCHANT, $user_id]])) {
			$user_id = null;
		}
		$builder = $this->modelsManager->createBuilder()
			->columns(['post_category_id' => 'a.id', 'a.name', 'b.body'])
			->from(['a' => 'Application\Models\PostCategory'])
			->leftJoin('Application\Models\Post', 'a.id = b.post_category_id AND b.user_id ' . ($user_id ? "= {$user_id}" : 'IS NULL'), 'b')
			->orderBy('a.id');
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page      = $paginator->getPaginate();
		$pages     = $this->_setPaginationRange($page);
		$posts     = [];
		$merchants = [];
		$result    = $this->db->query('SELECT id, company FROM users WHERE status = 1 AND role_id = ? AND premium_merchant = 1 ORDER BY company', [Role::MERCHANT]);
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$posts[] = $item;
		}
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$merchants[] = $row;
		}
		$this->view->menu      = $this->_menu('Content');
		$this->view->page      = $page;
		$this->view->pages     = $pages;
		$this->view->posts     = $posts;
		$this->view->merchants = $merchants;
		$this->view->user_id   = $user_id;
	}

	function saveAction() {
		if ($this->request->isPost()) {
			$user_id = filter_var($this->request->getPost('user_id'), FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
			$posts   = $this->request->getPost('posts');
			if ($user_id && !User::findFirst(['status = 1 AND role_id = ?0 AND premium_merchant = 1 AND id = ?1', 'bind' => [Role::MERCHANT, $user_id]])) {
				return $this->response->redirect('/admin/posts');
			}
			foreach ($posts as $post_category_id => $body) {
				if (!filter_var($post_category_id, FILTER_VALIDATE_INT) || !PostCategory::findFirst($post_category_id) || !$body) {
					continue;
				}
				$post = Post::findFirst(['user_id ' . ($user_id ? "= {$user_id}" : 'IS NULL') . ' AND post_category_id = ?0', 'bind' => [$post_category_id]]);
				if (!$post) {
					$post                   = new Post;
					$post->post_category_id = $post_category_id;
					$post->user_id          = $user_id;
				}
				$post->save(['body' => $body]);
			}
			$this->flashSession->success('Update konten berhasil.');
		}
		$this->dispatcher->forward(['action' => 'index']);
	}
}