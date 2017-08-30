<?php

namespace Application\Backend\Controllers;

use Application\Models\ProductGroup;
use Application\Models\ProductGroupMember;
use Ds\Set;
use Exception;
use Phalcon\Paginator\Adapter\QueryBuilder;

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
			->groupBy('a.id')
			->orderBy('a.name ASC');
		if ($keyword) {
			$builder->where('a.name ILIKE ?0', ["%{$keyword}%"]);
		}
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page           = $paginator->getPaginate();
		$pages          = $this->_setPaginationRange($page);
		$product_groups = new Set;
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$product_groups->add($item);
		}
		$this->view->keyword        = $keyword;
		$this->view->product_groups = $product_groups;
		$this->view->page           = $paginator->getPaginate();
		$this->view->pages          = $pages;
	}

	function createAction() {
		$product_group = new ProductGroup;
		if ($this->request->isPost()) {
			$product_group->name      = $this->request->getPost('name');
			$product_group->published = $this->request->getPost('published');
			if ($product_group->validation() && $product_group->create()) {
				$this->flashSession->success('Penambahan group produk berhasil.');
				return $this->response->redirect('/admin/product_groups');
			}
			$this->flashSession->error('Penambahan group produk tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($product_group->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->product_group = $product_group;
	}

	function updateAction($id) {
		if (!$product_group = ProductGroup::findFirst($id)) {
			$this->flashSession->error('Grup produk tidak ditemukan.');
			return $this->response->redirect('/admin/product_groups');
		}
		if ($this->request->isPost()) {
			$product_group->name      = $this->request->getPost('name');
			$product_group->published = $this->request->getPost('published');
			if ($product_group->validation() && $product_group->update()) {
				$this->flashSession->success('Update group produk berhasil.');
				return $this->response->redirect('/admin/product_groups');
			}
			$this->flashSession->error('Update group produk tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($product_group->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->product_group = $product_group;
	}

	function toggleStatusAction($id) {
		if ($this->request->isPost()) {
			if ($product_group = ProductGroup::findFirst($id)) {
				$product_group->update(['published' => $product_group->published ? 0 : 1]);
			} else {
				$this->flashSession->error('Grup produk tidak ditemukan!');
			}
		}
		return $this->response->redirect($this->request->get('next'));
	}

	function deleteAction($id) {
		try {
			if (!$product_group = ProductGroup::findFirst($id)) {
				throw new Error('Grup produk tidak ditemukan.');
			}
			if (ProductGroupMember::findFirstByProductGroupId($product_group->id)) {
				throw new Error('Grup produk tidak dapat dihapus.');
			}
			$product_group->delete();
			$this->flashSession->success('Grup produk berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect('/admin/product_groups');
		}
	}
}