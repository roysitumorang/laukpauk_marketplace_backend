<?php

namespace Application\Backend\Controllers;

use Application\Models\{ProductGroup, ProductGroupMember};
use Ds\Set;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\QueryBuilder;

class ProductGroupMembersController extends ControllerBase {
	private $_product_group;

	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		$product_categories = new Set;
		$product_groups     = new Set;
		if ($this->request->isGet()) {
			$result = $this->db->query('SELECT a.id, a.name, COUNT(b.product_id) AS total_products FROM product_groups a LEFT JOIN product_group_member b ON a.id = b.product_group_id GROUP BY a.id ORDER BY a.name');
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($item = $result->fetch()) {
				$product_groups->add($item);
			}
			if ($product_groups->isEmpty()) {
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
		$result = $this->db->query('SELECT a.id, a.name, COUNT(b.id) AS total_products FROM product_categories a LEFT JOIN products b ON a.id = b.product_category_id GROUP BY a.id ORDER BY a.name');
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($item = $result->fetch()) {
			$product_categories->add($item);
		}
		$this->view->setVars([
			'menu'               => $this->_menu('Products'),
			'product_group'      => $this->_product_group,
			'product_groups'     => $product_groups,
			'product_categories' => $product_categories,
		]);
	}

	function indexAction() {
		$product_category_id = $this->dispatcher->getParam('product_category_id', 'int');
		$keyword             = $this->dispatcher->getParam('keyword');
		$limit               = 16;
		$current_page        = $this->dispatcher->getParam('page', 'int') ?: 1;
		$products            = new Set;
		$builder             = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'a.name',
				'a.stock_unit',
				'a.published',
				'a.picture',
				'a.thumbnails',
			])
			->from(['a' => 'Application\Models\Product'])
			->join('Application\Models\UserProduct', 'a.id = b.product_id', 'b')
			->join('Application\Models\User', 'b.user_id = c.id', 'c')
			->join('Application\Models\ProductGroupMember', 'a.id = d.product_id AND d.product_group_id = ' . $this->_product_group->id, 'd')
			->where("d.product_group_id = {$this->_product_group->id}")
			->groupBy('a.id')
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
		$page  = $paginator->paginate();
		$pages = $this->_setPaginationRange($page);
		foreach ($page->items as $item) {
			if ($item->picture) {
				$item->thumbnails = explode(',', $item->thumbnails);
			}
			$products->add($item);
		}
		$this->view->setVars([
			'products'            => $products,
			'product_category_id' => $product_category_id,
			'keyword'             => $keyword,
			'page'                => $page,
			'pages'               => $pages,
		]);
	}

	function createAction() {
		$product_ids = new Set;
		if ($this->request->isPost() && ($product_ids = $this->request->getPost('product_ids'))) {
			foreach ($product_ids as $product_id) {
				if ($this->db->fetchColumn('SELECT COUNT(1) FROM products a JOIN user_product b ON a.id = b.product_id WHERE a.id = ?', [$product_id]) && !$this->db->fetchColumn('SELECT COUNT(1) FROM product_group_member WHERE product_group_id = ? AND product_id = ?', [$this->_product_group->id, $product_id])) {
					$link = new ProductGroupMember;
					$link->create(['product_group_id' => $this->_product_group->id, 'product_id' => $product_id]);
				}
			}
			$product_groups = new Set;
			$result         = $this->db->query('SELECT a.id, a.name, COUNT(b.product_id) AS total_products FROM product_groups a LEFT JOIN product_group_member b ON a.id = b.product_group_id GROUP BY a.id ORDER BY a.name');
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($item = $result->fetch()) {
				$product_groups->add($item);
			}
			$this->view->product_groups = $product_groups;
			$this->flashSession->success('Penambahan produk berhasil.');
		}
		$product_category_id = $this->dispatcher->getParam('product_category_id', 'int');
		$keyword             = $this->dispatcher->getParam('keyword');
		$limit               = 16;
		$current_page        = $this->dispatcher->getParam('page', 'int') ?: 1;
		$products            = new Set;
		$builder             = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'a.name',
				'a.stock_unit',
				'a.published',
				'a.picture',
				'a.thumbnails',
			])
			->from(['a' => 'Application\Models\Product'])
			->join('Application\Models\UserProduct', 'a.id = b.product_id', 'b')
			->join('Application\Models\User', 'b.user_id = c.id', 'c')
			->leftJoin('Application\Models\ProductGroupMember', 'a.id = d.product_id AND d.product_group_id = ' . $this->_product_group->id, 'd')
			->where('d.product_id IS NULL')
			->groupBy('a.id')
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
		$page  = $paginator->paginate();
		$pages = $this->_setPaginationRange($page);
		foreach ($page->items as $item) {
			if ($item->picture) {
				$item->thumbnails = explode(',', $item->thumbnails);
			}
			$products->add($item);
		}
		$this->view->setVars([
			'products'            => $products,
			'product_category_id' => $product_category_id,
			'product_ids'         => $product_ids,
			'keyword'             => $keyword,
			'page'                => $page,
			'pages'               => $pages,
		]);
	}

	function deleteAction() {
		try {
			$product_id = $this->dispatcher->getParam('product_id', 'int');
			if (!$product_id || !($link = ProductGroupMember::findFirst(['product_group_id = ?0 AND product_id = ?1', 'bind' => [$this->_product_group->id, $product_id]]))) {
				throw new \Exception('Produk tidak ditemukan.');
			}
			$link->delete();
			$this->flashSession->success('Produk berhasil dihapus');
		} catch (\Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect("/admin/product_group_members/index/product_group_id:{$this->_product_group->id}");
		}
	}

	function truncateAction() {
		$this->db->execute('DELETE FROM product_group_member WHERE product_group_id = ?', [$this->_product_group->id]);
		$this->flashSession->success('Semua produk berhasil dihapus');
		return $this->response->redirect("/admin/product_group_members/index/product_group_id:{$this->_product_group->id}");
	}
}