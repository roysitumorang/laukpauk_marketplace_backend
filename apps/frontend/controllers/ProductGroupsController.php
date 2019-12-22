<?php

namespace Application\Frontend\Controllers;

use Application\Models\ProductGroup;
use Application\Models\ProductGroupMember;
use Phalcon\Exception;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class ProductGroupsController extends ControllerBase {
	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		$this->view->menu = $this->_menu('Products');
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
			->from(['a' => 'Application\Models\ProductGroup'])
			->leftJoin('Application\Models\ProductGroupMember', 'a.id = b.product_group_id', 'b')
			->where("a.user_id = {$this->currentUser->id}")
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
		$page           = $paginator->paginate();
		$pages          = $this->_setPaginationRange($page);
		$product_groups = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$product_groups[] = $item;
		}
		$this->view->keyword        = $keyword;
		$this->view->product_groups = $product_groups;
		$this->view->page           = $paginator->paginate();
		$this->view->pages          = $pages;
	}

	function createAction() {
		$product_group = new ProductGroup;
		if ($this->request->isPost()) {
			$product_group->user_id   = $this->currentUser->id;
			$product_group->name      = $this->request->getPost('name');
			$product_group->published = $this->request->getPost('published');
			if ($product_group->validation() && $product_group->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect('/product_groups');
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($product_group->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->product_group = $product_group;
	}

	function updateAction($id) {
		$product_group = ProductGroup::findFirst(['user_id = ?0 AND id = ?1', 'bind' => [$this->currentUser->id, $id]]);
		if (!$product_group) {
			$this->flashSession->error('Group produk tidak ditemukan.');
			return $this->response->redirect('/product_groups');
		}
		if ($this->request->isPost()) {
			$product_group->name      = $this->request->getPost('name');
			$product_group->published = $this->request->getPost('published');
			if ($product_group->validation() && $product_group->update()) {
				$this->flashSession->success('Update data berhasil.');
				return $this->response->redirect('/product_groups');
			}
			$this->flashSession->error('Update data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($product_group->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->product_group = $product_group;
	}

	function publishAction($id) {
		if ($this->request->isPost()) {
			$product_group = ProductGroup::findFirst(['user_id = ?0 AND id = ?1 AND published = 0', 'bind' => [$this->currentUser->id, $id]]);
			if ($product_group) {
				$product_group->update(['published' => 1]);
			} else {
				$this->flashSession->error('Group produk tidak ditemukan!');
			}
		}
		return $this->response->redirect($this->request->get('next'));
	}

	function unpublishAction($id) {
		if ($this->request->isPost()) {
			$product_group = ProductGroup::findFirst(['user_id = ?0 AND id = ?1 AND published = 1', 'bind' => [$this->currentUser->id, $id]]);
			if ($product_group) {
				$product_group->update(['published' => 0]);
			} else {
				$this->flashSession->error('Group produk tidak ditemukan!');
			}
		}
		return $this->response->redirect($this->request->get('next'));
	}

	function deleteAction($id) {
		try {
			$product_group = ProductGroup::findFirst(['user_id = ?0 AND id = ?1', 'bind' => [$this->currentUser->id, $id]]);
			if (!$product_group) {
				throw new Exception('Data tidak ditemukan.');
			}
			if (ProductGroupMember::findFirstByProductGroupId($product_group->id)) {
				throw new Exception('Data tidak dapat dihapus.');
			}
			$product_group->delete();
			$this->flashSession->success('Data berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect('/product_groups');
		}
	}
}
