<?php

namespace Application\Backend\Controllers;

use Application\Models\ProductGroup;
use Application\Models\ProductGroupMember;
use Error;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\QueryBuilder;

class ProductGroupMembersController extends ControllerBase {
	private $_product_group;

	function onConstruct() {
		$product_categories = [];
		$product_groups     = [];
		if ($this->request->isGet()) {
			$result = $this->db->query('SELECT a.id, a.name, COUNT(b.product_id) AS total_products FROM product_groups a LEFT JOIN product_group_member b ON a.id = b.product_group_id WHERE a.user_id IS NULL GROUP BY a.id ORDER BY a.name');
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($item = $result->fetch()) {
				$product_groups[] = $item;
			}
			if (!$product_groups) {
				$this->flashSession->error('Belum ada group produk.');
				return $this->response->redirect('/admin/product_groups');
			}
		}
		$product_group_id = $this->dispatcher->getParam('product_group_id', 'int');
		if ($product_group_id) {
			$this->_product_group = ProductGroup::findFirst($product_group_id);
		}
		if (!$this->_product_group) {
			$this->_product_group = ProductGroup::findFirst($product_groups[0]->id);
		}
		$result = $this->db->query('SELECT a.id, a.name, COUNT(b.id) AS total_products FROM product_categories a LEFT JOIN products b ON a.id = b.product_category_id WHERE a.user_id IS NULL GROUP BY a.id ORDER BY a.name');
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($item = $result->fetch()) {
			$product_categories[] = $item;
		}
		$this->view->menu               = $this->_menu('Options');
		$this->view->product_group      = $this->_product_group;
		$this->view->product_groups     = $product_groups;
		$this->view->product_categories = $product_categories;
	}

	function indexAction() {
		$product_category_id = $this->dispatcher->getParam('product_category_id', 'int');
		$keyword             = $this->dispatcher->getParam('keyword');
		$limit               = 16;
		$current_page        = $this->dispatcher->getParam('page', 'int') ?: 1;
		$products            = [];
		$builder = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'a.name',
				'a.stock_unit',
				'a.price',
				'a.published',
				'a.picture',
				'a.thumbnails',
			])
			->from(['a' => 'Application\Models\Product'])
			->join('Application\Models\User', 'a.user_id = b.id', 'b')
			->join('Application\Models\ProductGroupMember', 'a.id = c.product_id', 'c')
			->where('b.premium_merchant IS NULL')
			->andWhere('c.product_group_id = ?0', [$this->_product_group->id])
			->orderBy('a.name ASC, a.stock_unit ASC');
		if ($product_category_id) {
			$builder->andWhere("a.product_category_id = {$product_category_id}");
		}
		if ($keyword) {
			$builder->andWhere("a.name ILIKE '%{$keyword}%'");
		}
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page  = $paginator->getPaginate();
		$pages = $this->_setPaginationRange($page);
		foreach ($page->items as $item) {
			if ($item->picture) {
				$item->thumbnails = explode(',', $item->thumbnails);
			}
			$products[] = $item;
		}
		$this->view->products            = $products;
		$this->view->product_category_id = $product_category_id;
		$this->view->keyword             = $keyword;
		$this->view->page                = $page;
		$this->view->pages               = $pages;
	}

	function createAction() {
		$product_ids = [];
		if ($this->request->isPost() && ($product_ids = $this->request->getPost('product_ids'))) {
			foreach ($product_ids as $product_id) {
				if ($this->db->fetchColumn('SELECT COUNT(1) FROM products a JOIN users b ON a.user_id = b.id WHERE a.id = ? AND b.premium_merchant IS NULL', [$product_id]) && !$this->db->fetchColumn('SELECT COUNT(1) FROM product_group_member WHERE product_group_id = ? AND product_id = ?', [$this->_product_group->id, $product_id])) {
					$link = new ProductGroupMember;
					$link->create(['product_group_id' => $this->_product_group->id, 'product_id' => $product_id]);
				}
			}
			$product_groups = [];
			$result         = $this->db->query('SELECT a.id, a.name, COUNT(b.product_id) AS total_products FROM product_groups a LEFT JOIN product_group_member b ON a.id = b.product_group_id GROUP BY a.id ORDER BY a.name');
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($item = $result->fetch()) {
				$product_groups[] = $item;
			}
			$this->view->product_groups = $product_groups;
			$this->flashSession->success('Penambahan data berhasil.');
		}
		$product_category_id = $this->dispatcher->getParam('product_category_id', 'int');
		$keyword             = $this->dispatcher->getParam('keyword');
		$limit               = 16;
		$current_page        = $this->dispatcher->getParam('page', 'int') ?: 1;
		$products            = [];
		$builder = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'a.name',
				'a.stock_unit',
				'a.price',
				'a.published',
				'a.picture',
				'a.thumbnails',
			])
			->from(['a' => 'Application\Models\Product'])
			->join('Application\Models\User', 'a.user_id = b.id', 'b')
			->leftJoin('Application\Models\ProductGroupMember', 'a.id = c.product_id AND c.product_group_id = ' . $this->_product_group->id, 'c')
			->where('b.premium_merchant IS NULL')
			->andWhere('c.product_id IS NULL')
			->orderBy('a.name ASC, a.stock_unit ASC');
		if ($product_category_id) {
			$builder->andWhere("a.product_category_id = {$product_category_id}");
		}
		if ($keyword) {
			$builder->andWhere("a.name ILIKE '%{$keyword}%'");
		}
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page  = $paginator->getPaginate();
		$pages = $this->_setPaginationRange($page);
		foreach ($page->items as $item) {
			if ($item->picture) {
				$item->thumbnails = explode(',', $item->thumbnails);
			}
			$products[] = $item;
		}
		$this->view->products            = $products;
		$this->view->product_category_id = $product_category_id;
		$this->view->product_ids         = $product_ids;
		$this->view->keyword             = $keyword;
		$this->view->page                = $page;
		$this->view->pages               = $pages;
	}

	function deleteAction() {
		try {
			$product_id = $this->dispatcher->getParam('product_id', 'int');
			if (!$product_id || !($link = ProductGroupMember::findFirst(['group_id = ?0 AND product_id = ?1', 'bind' => [$this->_product_group->id, $product_id]]))) {
				throw new Error('Data tidak ditemukan.');
			}
			$link->delete();
			$this->flashSession->success('Data berhasil dihapus');
		} catch (Error $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect("/admin/product_group_members/index/product_group_id:{$this->_product_group->id}");
		}
	}

	function truncateAction() {
		$this->db->execute('DELETE FROM product_group_member WHERE product_group_id = ?', [$this->_product_group->id]);
		$this->flashSession->success('Data berhasil dihapus');
		return $this->response->redirect("/admin/product_group_members/index/product_group_id:{$this->_product_group->id}");
	}
}