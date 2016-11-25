<?php

namespace Application\Backend\Controllers;

use Application\Models\Brand;
use Application\Models\Product;
use Application\Models\ProductCategory;
use Application\Models\ProductPicture;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class ProductsController extends BaseController {
	function indexAction() {
		$limit               = $this->config->per_page;
		$current_page        = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset              = ($current_page - 1) * $limit;
		$keyword             = $this->request->getQuery('keyword', 'string');
		$field               = $this->request->getQuery('field', 'string');
		$product_category_id = $this->request->getQuery('product_category_id', 'int');
		$brand_id            = $this->request->getQuery('brand_id', 'int');
		$search_fields       = [
			'id'          => 'ID Produk',
			'name'        => 'Nama Produk',
			'description' => 'Detail Produk',
			'price'       => 'Harga Produk',
			'created_at'  => 'Tanggal Upload',
			'status'      => 'Status (tersedia / habis)',
			'published'   => 'Show (tampil / sembunyi)',
		];
		$this->_prepare_categories_and_brands();
		if ($keyword && $field && array_key_exists($field, $search_fields)) {
			$parameter['conditions']      = "{$field} LIKE :keyword:";
			$parameter['bind']['keyword'] = "%{$keyword}%";
		}
		if ($product_category_id) {
			$parameter['conditions']                 .= ($parameter['conditions'] ? ' AND ' : '') . "product_category_id = :product_category_id:";
			$parameter['bind']['product_category_id'] = $product_category_id;
		}
		if ($brand_id) {
			$parameter['conditions']      .= ($parameter['conditions'] ? ' AND ' : '') . "brand_id = :brand_id:";
			$parameter['bind']['brand_id'] = $brand_id;
		}
		$paginator = new PaginatorModel([
			'data'  => Product::find($parameter['conditions'] ? $parameter : []),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page      = $paginator->getPaginate();
		$pages     = $this->_setPaginationRange($page);
		$products  = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$picture = $item->pictures->getFirst();
			if ($picture) {
				$item->writeAttribute('thumbnail', $picture->getThumbnail(120, 100));
			}
			$products[] = $item;
		}
		$this->view->menu                = $this->_menu('Products');
		$this->view->keyword             = $keyword;
		$this->view->page                = $page;
		$this->view->pages               = $pages;
		$this->view->products            = $products;
		$this->view->search_fields       = $search_fields;
		$this->view->field               = $field;
		$this->view->product_category_id = $product_category_id;
		$this->view->brand_id            = $brand_id;
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
		$this->_prepare_categories_and_brands();
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
			if ($this->dispatcher->hasParam('delete_picture')) {
				$picture_id = $this->dispatcher->getParam('delete_picture');
				$picture    = $product->pictures->filter(function($picture) use($picture_id) {
					if ($picture->id == $picture_id) {
						return $picture;
					}
				})[0];
				$picture && $picture->delete();
				return $this->response->redirect("/admin/products/update/{$product->id}");
			}
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
		$pictures = [];
		foreach ($product->pictures as $picture) {
			$picture->thumbnail = $picture->getThumbnail(600, 450);
			$pictures[]         = $picture;
		}
		$this->_prepare_categories_and_brands();
		$this->view->menu     = $this->_menu('Products');
		$this->view->product  = $product;
		$this->view->pictures = $pictures;
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

	private function _prepare_categories_and_brands() {
		$categories = [];
		$brands     = [];
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
		$resultset = $this->db->query('SELECT a.id, a.name, COUNT(b.id) AS total_products FROM brands a LEFT JOIN products b ON a.id = b.brand_id GROUP BY a.id ORDER BY a.name');
		$resultset->setFetchMode(Db::FETCH_OBJ);
		while ($row = $resultset->fetch()) {
			$brands[] = $row;
		}
		$this->view->categories = $categories;
		$this->view->brands     = $brands;
	}

	private function _set_model_attributes(&$product) {
		$product->category = ProductCategory::findFirst($this->request->getPost('product_category_id'));
		$product->brand    = Brand::findFirst($this->request->getPost('brand_id'));
		$product->setCode($this->request->getPost('code'));
		$product->setName($this->request->getPost('name'));
		$product->setStock($this->request->getPost('stock'));
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
		$product->setUnitOfMeasure($this->request->getPost('unit_of_measure'));
		$pictures = [];
		for ($i = 0; $i < 5; $i++) {
			$picture_id = filter_var($_POST['product_pictures'][$i]['id'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
			$picture    = ProductPicture::findFirstById($picture_id) ?: new ProductPicture;
			$position   = filter_var($_POST['product_pictures'][$i]['position'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
			$picture->setNewFile([
				'name'     => $_FILES['product_pictures']['name'][$i],
				'type'     => $_FILES['product_pictures']['type'][$i],
				'tmp_name' => $_FILES['product_pictures']['tmp_name'][$i],
				'error'    => $_FILES['product_pictures']['error'][$i],
				'size'     => $_FILES['product_pictures']['size'][$i],
			]);
			$picture->setPosition($i + 1);
			if ($picture->id) {
				$picture->thumbnail = $picture->getThumbnail(600, 450);
			}
			$pictures[] = $picture;
		}
		$product->pictures = $pictures;
	}
}