<?php

namespace Application\Backend\Controllers;

use Application\Models\Group;
use Error;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class GroupsController extends ControllerBase {
	function onConstruct() {
		$this->view->menu = $this->_menu('Options');
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$keyword      = $this->dispatcher->getParam('keyword');
		$builder      = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'a.name',
				'a.published',
				'total_products' => 'COUNT(b.product_id)',
			])
			->from(['a' => 'Application\Models\Group'])
			->leftJoin('Application\Models\ProductGroup', 'a.id = b.group_id', 'b')
			->groupBy('a.id')
			->orderBy('a.name ASC');
		if ($keyword) {
			$builder->andWhere('a.name ILIKE ?0', ["%{$keyword}%"]);
		}
		$paginator = new PaginatorQueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page   = $paginator->getPaginate();
		$pages  = $this->_setPaginationRange($page);
		$groups = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$groups[] = $item;
		}
		$this->view->keyword = $keyword;
		$this->view->groups  = $groups;
		$this->view->page    = $paginator->getPaginate();
		$this->view->pages   = $pages;
	}

	function createAction() {
		$group = new Group;
		if ($this->request->isPost()) {
			$group->name      = $this->request->getPost('name');
			$group->published = $this->request->getPost('published');
			if ($group->validation() && $group->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect('/admin/groups');
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($group->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->group = $group;
	}

	function updateAction($id) {
		$group = Group::findFirst($id);
		if (!$group) {
			$this->flashSession->error('Group produk tidak ditemukan.');
			return $this->dispatcher->forward('groups');
		}
		if ($this->request->isPost()) {
			$group->name      = $this->request->getPost('name');
			$group->published = $this->request->getPost('published');
			if ($group->validation() && $group->update()) {
				$this->flashSession->success('Update data berhasil.');
				return $this->response->redirect('/admin/groups');
			}
			$this->flashSession->error('Update data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($group->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->group = $group;
	}

	function publishAction($id) {
		if ($this->request->isPost()) {
			$group = Group::findFirst(['id = ?0 AND published = ?1', 'bind' => [$id, 0]]);
			if ($group) {
				$group->update(['published' => 1]);
			} else {
				$this->flashSession->error('Group produk tidak ditemukan!');
			}
		}
		return $this->response->redirect($this->request->get('next'));
	}

	function unpublishAction($id) {
		if ($this->request->isPost()) {
			$group = Group::findFirst(['id = ?0 AND published = ?1', 'bind' => [$id, 1]]);
			if ($group) {
				$group->update(['published' => 0]);
			} else {
				$this->flashSession->error('Group produk tidak ditemukan!');
			}
		}
		return $this->response->redirect($this->request->get('next'));
	}

	function deleteAction($id) {
		try {
			$group = Group::findFirst($id);
			if (!$group) {
				throw new Error('Data tidak ditemukan.');
			}
			if ($group->products) {
				throw new Error('Data tidak dapat dihapus.');
			}
			$group->delete();
			$this->flashSession->success('Data berhasil dihapus');
		} catch (Error $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect('/admin/groups');
		}
	}
}
