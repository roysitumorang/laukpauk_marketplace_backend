<?php

namespace Application\Backend\Controllers;

use Application\Models\Group;
use Application\Models\ProductGroup;
use Error;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\QueryBuilder;

class GroupProductsController extends ControllerBase {
	private $_group;

	function onConstruct() {
		$product_categories = [];
		$groups             = [];
		$result             = $this->db->query('SELECT a.id, a.name, COUNT(b.product_id) AS total_products FROM groups a LEFT JOIN product_group b ON a.id = b.group_id GROUP BY a.id ORDER BY a.name');
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($item = $result->fetch()) {
			$groups[] = $item;
		}
		if (!$groups) {
			$this->flashSession->error('Belum ada group.');
			return $this->response->redirect('/admin/groups');
		}
		$group_id = $this->dispatcher->getParam('group_id', 'int');
		if ($group_id) {
			$this->_group = Group::findFirst($group_id);
		}
		if (!$this->_group) {
			$this->_group = Group::findFirst($groups[0]->id);
		}
		$result = $this->db->query('SELECT a.id, a.name, COUNT(b.id) AS total_products FROM product_categories a LEFT JOIN products b ON a.id = b.product_category_id WHERE a.user_id IS NULL GROUP BY a.id ORDER BY a.name');
		$result->setFetchMode(Db::FETCH_OBJ);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($item = $result->fetch()) {
			$product_categories[] = $item;
		}
		$this->view->menu               = $this->_menu('Options');
		$this->view->group              = $this->_group;
		$this->view->groups             = $groups;
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
			->join('Application\Models\ProductGroup', 'a.id = c.product_id', 'c')
			->where('b.premium_merchant IS NULL')
			->andWhere("c.group_id = {$this->_group->id}")
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
				if ($this->db->fetchColumn('SELECT COUNT(1) FROM products a JOIN users b ON a.user_id = b.id WHERE a.id = ? AND b.premium_merchant IS NULL', [$product_id]) && !$this->db->fetchColumn('SELECT COUNT(1) FROM product_group WHERE group_id = ? AND product_id = ?', [$this->_group->id, $product_id])) {
					$link = new ProductGroup;
					$link->create(['group_id' => $this->_group->id, 'product_id' => $product_id]);
				}
			}
			$groups = [];
			$result = $this->db->query('SELECT a.id, a.name, COUNT(b.product_id) AS total_products FROM groups a LEFT JOIN product_group b ON a.id = b.group_id GROUP BY a.id ORDER BY a.name');
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($item = $result->fetch()) {
				$groups[] = $item;
			}
			$this->view->groups = $groups;
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
			->leftJoin('Application\Models\ProductGroup', 'a.id = c.product_id AND c.group_id = ' . $this->_group->id, 'c')
			->where('b.premium_merchant IS NULL')
			->andWhere('c.product_id IS NULL')
			->orderBy('a.name ASC, a.stock_unit ASC');
		if ($product_category_id) {
			$builder->andWhere('a.product_category_id = ' . $product_category_id);
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
			if (!$product_id || !($link = ProductGroup::findFirst(['group_id = ?0 AND product_id = ?1', 'bind' => [$this->_group->id, $product_id]]))) {
				throw new Error('Data tidak ditemukan.');
			}
			$link->delete();
			$this->flashSession->success('Data berhasil dihapus');
		} catch (Error $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect("/admin/group_products/index/group_id:{$this->_group->id}");
		}
	}

	function truncateAction() {
		$this->db->execute('DELETE FROM product_group WHERE group_id = ?', [$this->_group->id]);
		$this->flashSession->success('Data berhasil dihapus');
		return $this->response->redirect("/admin/group_products/index/group_id:{$this->_group->id}");
	}
}