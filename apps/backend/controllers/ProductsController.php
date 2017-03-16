<?php

namespace Application\Backend\Controllers;

use Application\Models\Product;
use Application\Models\ProductCategory;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class ProductsController extends ControllerBase {
	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$category_id  = $this->dispatcher->getParam('category_id', 'int');
		$published    = filter_var($this->dispatcher->getParam('published'), FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
		$keyword      = $this->dispatcher->getParam('keyword', 'string');
		$parameter    = [];
		$conditions   = [[]];
		$this->_prepare_datas();
		if (filter_var($keyword, FILTER_VALIDATE_INT)) {
			$conditions[0][]  = 'id = :id:';
			$conditions['id'] = $keyword;
		} else if ($keyword) {
			$conditions[0][]    = 'name LIKE :name:';
			$conditions['name'] = '%' . $keyword . '%';
		}
		if ($category_id) {
			$conditions[0][]           = 'product_category_id = :category_id:';
			$conditions['category_id'] = $category_id;
		}
		if (is_int($published)) {
			$conditions[0][]         = 'published = :published:';
			$conditions['published'] = $published;
		}
		if ($conditions[0]) {
			$parameter['conditions'] = implode(' AND ', array_shift($conditions));
			$parameter['bind']       = $conditions;
		}
		$paginator = new PaginatorModel([
			'data'  => Product::find($parameter),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page     = $paginator->getPaginate();
		$pages    = $this->_setPaginationRange($page);
		$products = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$products[] = $item;
		}
		$this->view->menu        = $this->_menu('Products');
		$this->view->page        = $page;
		$this->view->pages       = $pages;
		$this->view->products    = $products;
		$this->view->keyword     = $keyword;
		$this->view->category_id = $category_id;
		$this->view->published   = $published;
	}

	function createAction() {
		$product = new Product;
		if ($this->request->isPost()) {
			$this->_set_model_attributes($product);
			if ($product->validation() && $product->create()) {
				$this->flashSession->success('Penambahan produk berhasil.');
				return $this->response->redirect('/admin/products');
			}
			$this->flashSession->error('Penambahan produk tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($product->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_datas();
		$this->view->menu     = $this->_menu('Products');
		$this->view->product  = $product;
		$this->view->pictures = [];
	}

	function updateAction($id) {
		if (!$product = Product::findFirst($id)) {
			$this->flashSession->error('Produk tidak ditemukan.');
			return $this->dispatcher->forward('products');
		}
		$next = $this->request->get('next');
		if ($this->request->isPost()) {
			$this->_set_model_attributes($product);
			if ($product->validation() && $product->update()) {
				$this->flashSession->success('Update produk berhasil.');
				return $this->response->redirect($next);
			}
			$this->flashSession->error('Update produk tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($product->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_datas();
		$this->view->menu       = $this->_menu('Products');
		$this->view->product    = $product;
		$this->view->active_tab = 'product';
		$this->view->next       = $next;
	}

	function publishAction($id) {
		if ($this->request->isPost()) {
			if (!$product = Product::findFirst(['id = ?0 AND published = 0', 'bind' => [$id]])) {
				$this->flashSession->error('Produk tidak ditemukan.');
			} else {
				$product->update(['published' => 1]);
				$this->flashSession->success('Produk berhasil ditampilkan');
			}
		}
		return $this->response->redirect($this->request->getQuery('next'));
	}

	function unpublishAction($id) {
		if ($this->request->isPost()) {
			if (!$product = Product::findFirst(['id = ?0 AND published = 1', 'bind' => [$id]])) {
				$this->flashSession->error('Produk tidak ditemukan.');
			} else {
				$product->update(['published' => 0]);
				$this->flashSession->success('Produk berhasil disembunyikan');
			}
		}
		return $this->response->redirect($this->request->getQuery('next'));
	}

	function deleteAction($id) {
		if ($this->request->isPost()) {
			if (!$product = Product::findFirstById($id)) {
				$this->flashSession->error('Produk tidak ditemukan.');
			} else {
				$product->delete();
				$this->flashSession->success('Produk berhasil dihapus');
			}
		}
		return $this->response->redirect('/admin/products');
	}

	private function _prepare_datas() {
		$categories = [];
		$resultset = $this->db->query('SELECT a.id, a.parent_id, a.name, COUNT(b.id) AS total_products FROM product_categories a LEFT JOIN products b ON a.id = b.product_category_id WHERE a.parent_id IS NULL GROUP BY a.id ORDER BY a.name ASC');
		$resultset->setFetchMode(Db::FETCH_OBJ);
		while ($row = $resultset->fetch()) {
			$sub_categories = [];
			$sub_resultset  = $this->db->query("SELECT a.id, a.parent_id, a.name, COUNT(b.id) AS total_products FROM product_categories a LEFT JOIN products b ON a.id = b.product_category_id WHERE a.parent_id = {$row->id} GROUP BY a.id ORDER BY a.name ASC");
			$sub_resultset->setFetchMode(Db::FETCH_OBJ);
			while ($sub_row = $sub_resultset->fetch()) {
				$row->total_products += $sub_row->total_products;
				$sub_categories[]     = $sub_row;
			}
			$categories[] = $row;
			$categories   = array_merge($categories, $sub_categories);
		}
		$this->view->categories = $categories;
		$this->view->lifetimes  = range(1, 30);
	}

	private function _set_model_attributes(&$product) {
		$product->category = ProductCategory::findFirst($this->request->getPost('product_category_id'));
		$product->setName($this->request->getPost('name'));
		$product->setStockUnit($this->request->getPost('stock_unit'));
		$product->setDescription($this->request->getPost('description'));
		$product->setLifetime($this->request->getPost('lifetime'));
		$product->setPublished($this->request->getPost('published'));
	}
}