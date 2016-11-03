<?php

namespace Application\Backend\Controllers;

use Application\Models\Product;
use Application\Models\ProductCategory;
use Exception;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class ProductsController extends BaseController {
	function indexAction() {
		$limit              = $this->config->per_page;
		$current_page       = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset             = ($current_page - 1) * $limit;
		$keyword            = $this->request->getQuery('keyword', 'string');
		$search_fields      = [
			'id'          => 'ID Produk',
			'name'        => 'Nama Produk',
			'description' => 'Detail Produk',
			'price'       => 'Harga Produk',
			'created_at'  => 'Tanggal Upload',
			'status'      => 'Status (tersedia / habis)',
			'published'   => 'Show (tampil / sembunyi)',
		];
		$product_categories = [];
		$brands             = [];
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
			$product_categories[] = $row;
			$product_categories   = array_merge($product_categories, $sub_categories);
		}
		$resultset = $this->db->query('SELECT a.id, a.name, COUNT(b.id) AS total_products FROM brands a LEFT JOIN products b ON a.id = b.brand_id GROUP BY a.id ORDER BY a.name');
		$resultset->setFetchMode(Db::FETCH_OBJ);
		while ($row = $resultset->fetch()) {
			$brands[] = $row;
		}
		if ($keyword = $this->request->getQuery('keyword', 'string') && $field = $this->request->getQuery('field', 'string') && array_key_exists($search_fields, $field)) {
			$parameter['conditions']      = "{$field} LIKE :keyword:";
			$parameter['bind']['keyword'] = "%{$keyword}%";
		}
		if ($product_category_id = $this->request->getQuery('product_category_id', 'int')) {
			$parameter['conditions']                  = ($parameter['conditions'] ? ' AND ' : '') . "product_category_id = :product_category_id:";
			$parameter['bind']['product_category_id'] = $product_category_id;
		}
		if ($brand_id = $this->request->getQuery('brand_id', 'int')) {
			$parameter['conditions']       = ($parameter['conditions'] ? ' AND ' : '') . "brand_id = :brand_id:";
			$parameter['bind']['brand_id'] = $brand_id;
		}
		$paginator     = new PaginatorModel([
			'data'  => Product::find($parameter['conditions'] ? $parameter : []),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page          = $paginator->getPaginate();
		$pages         = $this->_setPaginationRange($page);
		foreach ($page->items as $item) {
			$thumbnail = $item->getThumbnail(120, 100, 'no_picture_120.png');
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('thumbnail', $thumbnail);
		}
		$this->view->menu                = $this->_menu('Products');
		$this->view->keyword             = $keyword;
		$this->view->page                = $paginator->getPaginate();
		$this->view->pages               = $pages;
		$this->view->search_fields       = $search_fields;
		$this->view->field               = $field;
		$this->view->product_categories  = $product_categories;
		$this->view->product_category_id = $product_category_id;
		$this->view->brands              = $brands;
		$this->view->brand_id            = $brand_id;
	}

	function showAction($id) {
		if (!filter_var($id, FILTER_VALIDATE_INT) || !($category = ProductCategory::findFirst($id))) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('products');
		}
	}

	function createAction() {
		$product            = new Product;
		$product_categories = [];
		$brands             = [];
		if ($this->request->isPost()) {
			$product->setCode($this->request->getPost('code'));
			$product->setName($this->request->getPost('name'));
			$product->setStock($this->request->getPost('stock'));
			$product->setProductCategoryId($this->request->getPost('product_category_id'));
			$product->setBrandId($this->request->getPost('brand_id'));
			$product->setPrice($this->request->getPost('price'));
			$product->setWeight($this->request->getPost('weight'));
			$product->setDescription($this->request->getPost('description'));
			$product->setNewPermalink($this->request->getPost('new_permalink'));
			$product->setPublished($this->request->getPost('published'));
			$product->setStatus($this->request->getPost('status'));
			$product->setBuyPoint($this->request->getPost('buy_point'));
			$product->setAffiliatePoint($this->request->getPost('affiliate_point'));
			$product->setMetaTitle($this->request->getPost('meta_title'));
			$product->setMetaDesc($this->request->getPost('meta_desc'));
			$product->setMetaKeyword($this->request->getPost('meta_keyword'));
			if ($product->validation() && $product->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect('/admin/products');
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($product->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
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
			$product_categories[] = $row;
			$product_categories   = array_merge($product_categories, $sub_categories);
		}
		$resultset = $this->db->query('SELECT a.id, a.name, COUNT(b.id) AS total_products FROM brands a LEFT JOIN products b ON a.id = b.brand_id GROUP BY a.id ORDER BY a.name');
		$resultset->setFetchMode(Db::FETCH_OBJ);
		while ($row = $resultset->fetch()) {
			$brands[] = $row;
		}
		$this->view->product            = $product;
		$this->view->product_categories = $product_categories;
		$this->view->brands             = $brands;
		$this->view->menu               = $this->_menu('Products');
	}

	function updateAction($id) {
		if (!filter_var($id, FILTER_VALIDATE_INT) || !($product = Product::findFirst($id))) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('products');
		}
		$product_categories = [];
		$brands             = [];
		foreach ($product->product_pictures as $picture) {
			$picture->thumbnail = $product->getThumbnail(600, 450);
		}
		if ($this->request->isPost()) {
			if ($this->dispatcher->hasParam('delete_picture')) {
				$product->deletePicture();
				return $this->response->redirect("/admin/products/update/{$category->id}");
			}
			if ($this->dispatcher->hasParam('published')) {
				$product->save(['published' => $product->published ? 0 : 1]);
				return $this->response->redirect($this->request->getQuery('next'));
			}
			$product->setCode($this->request->getPost('code'));
			$product->setName($this->request->getPost('name'));
			$product->setStock($this->request->getPost('stock'));
			$product->setProductCategoryId($this->request->getPost('product_category_id'));
			$product->setBrandId($this->request->getPost('brand_id'));
			$product->setPrice($this->request->getPost('price'));
			$product->setWeight($this->request->getPost('weight'));
			$product->setDescription($this->request->getPost('description'));
			$product->setNewPermalink($this->request->getPost('new_permalink'));
			$product->setPublished($this->request->getPost('published'));
			$product->setStatus($this->request->getPost('status'));
			$product->setBuyPoint($this->request->getPost('buy_point'));
			$product->setAffiliatePoint($this->request->getPost('affiliate_point'));
			$product->setMetaTitle($this->request->getPost('meta_title'));
			$product->setMetaDesc($this->request->getPost('meta_desc'));
			$product->setMetaKeyword($this->request->getPost('meta_keyword'));
			if ($product->validation() && $product->create()) {
				$this->flashSession->success('Update data berhasil.');
				return $this->response->redirect("/admin/products/update/{$category->id}");
			}
			$this->flashSession->error('Update data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($product->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
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
			$product_categories[] = $row;
			$product_categories   = array_merge($product_categories, $sub_categories);
		}
		$resultset = $this->db->query('SELECT a.id, a.name, COUNT(b.id) AS total_products FROM brands a LEFT JOIN products b ON a.id = b.brand_id GROUP BY a.id ORDER BY a.name');
		$resultset->setFetchMode(Db::FETCH_OBJ);
		while ($row = $resultset->fetch()) {
			$brands[] = $row;
		}
		$this->view->product            = $product;
		$this->view->product_categories = $product_categories;
		$this->view->brands             = $brands;
		$this->view->menu               = $this->_menu('Products');
	}

	function deleteAction($id) {
		try {
			if (!filter_var($id, FILTER_VALIDATE_INT) || !($product = Product::findFirst($id))) {
				throw new Exception('Data tidak ditemukan.');
			}
			$product->delete();
			$this->flashSession->success('Data berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect('/admin/products');
		}
	}
}