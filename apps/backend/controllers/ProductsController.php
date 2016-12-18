<?php

namespace Application\Backend\Controllers;

use Application\Models\Product;
use Application\Models\ProductCategory;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class ProductsController extends ControllerBase {
	function indexAction() {
		$limit               = $this->config->per_page;
		$current_page        = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset              = ($current_page - 1) * $limit;
		$id                  = $this->request->getQuery('id', 'int');
		$name                = $this->request->getQuery('name', 'string');
		$product_category_id = $this->request->getQuery('product_category_id', 'int');
		$published           = filter_var($this->request->getQuery('published'), FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
		$parameter           = [];
		$query_string_params = [];
		$conditions          = [[]];
		$this->_prepare_categories();
		if ($id) {
			$conditions[0][]           = 'id = :id:';
			$conditions['id']          = $id;
			$query_string_params['id'] = $id;
		}
		if ($name) {
			$conditions[0][]             = 'name LIKE :name:';
			$conditions['name']          = '%' . $name . '%';
			$query_string_params['name'] = $name;
		}
		if ($product_category_id) {
			$conditions[0][]                            = 'product_category_id = :product_category_id:';
			$query_string_params['product_category_id'] = $product_category_id;
		}
		if ($published) {
			$conditions[0][]                  = 'published = :published:';
			$conditions['published']          = $published;
			$query_string_params['published'] = $published;
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
		$page      = $paginator->getPaginate();
		$pages     = $this->_setPaginationRange($page);
		$products  = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$products[] = $item;
		}
		$this->view->menu                = $this->_menu('Products');
		$this->view->page                = $page;
		$this->view->pages               = $pages;
		$this->view->products            = $products;
		$this->view->id                  = $id;
		$this->view->name                = $name;
		$this->view->product_category_id = $product_category_id;
		$this->view->published           = $published;
		$this->view->query_string        = http_build_query($query_string_params);
	}

	function showAction($id) {
		if (!$category = ProductCategory::findFirst($id)) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('products');
		}
	}

	function createAction() {
		$product  = new Product;
		if ($this->request->isPost()) {
			$this->_set_model_attributes($product);
			if ($product->validation() && $product->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect('/admin/products');
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($product->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_categories();
		$this->view->menu     = $this->_menu('Products');
		$this->view->product  = $product;
		$this->view->pictures = [];
	}

	function updateAction($id) {
		if (!$product = Product::findFirst($id)) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('products');
		}
		if ($this->request->isPost()) {
			if ($this->dispatcher->hasParam('published')) {
				$product->save(['published' => $product->published ? 0 : 1]);
				return $this->response->redirect($this->request->getQuery('next'));
			}
			$this->_set_model_attributes($product);
			if ($product->validation() && $product->update()) {
				$this->flashSession->success('Update data berhasil.');
				return $this->response->redirect("/admin/products/update/{$product->id}");
			}
			$this->flashSession->error('Update data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($product->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_categories();
		$this->view->menu         = $this->_menu('Products');
		$this->view->product      = $product;
	}

	function deleteAction($id) {
		if (!$product = Product::findFirst($id)) {
			$this->flashSession->error('Data tidak ditemukan.');
		} else {
			$product->delete();
			$this->flashSession->success('Data berhasil dihapus');
		}
		return $this->response->redirect('/admin/products');
	}

	private function _prepare_categories() {
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
	}

	private function _set_model_attributes(&$product) {
		$product->category = ProductCategory::findFirst($this->request->getPost('product_category_id'));
		$product->setName($this->request->getPost('name'));
		$product->setDescription($this->request->getPost('description'));
		$product->setPublished($this->request->getPost('published'));
	}
}