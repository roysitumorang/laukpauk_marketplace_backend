<?php

namespace Application\Frontend\Controllers;

use Application\Models\Product;
use Application\Models\ProductCategory;
use Application\Models\UserProduct;
use Phalcon\Db\Enum;
use Phalcon\Exception;
use Phalcon\Paginator\Adapter\Model;

class ProductsController extends ControllerBase {
	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$category_id  = $this->dispatcher->getParam('category_id', 'int');
		$published    = filter_var($this->dispatcher->getParam('published'), FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
		$keyword      = $this->dispatcher->getParam('keyword', 'string');
		$next         = '/products/index';
		$conditions   = ['user_id = :user_id:', 'bind' => ['user_id' => $this->currentUser->id]];
		$this->_prepare_datas();
		if ($category_id) {
			$conditions[0]                    .= ' AND product_category_id = :category_id:';
			$conditions['bind']['category_id'] = $category_id;
			$next                             .= "/category_id:{$category_id}";
		}
		if (is_int($published)) {
			$conditions[0]                  .= ' AND published = :published:';
			$conditions['bind']['published'] = $published;
			$next                           .= "/published:{$published}";
		}
		if (filter_var($keyword, FILTER_VALIDATE_INT)) {
			$conditions[0]           .= ' AND id = :id:';
			$conditions['bind']['id'] = $keyword;
			$next                    .= "/keyword:{$keyword}";
		} else if ($keyword) {
			$conditions[0]             .= ' AND name ILIKE :name:';
			$conditions['bind']['name'] = '%' . $keyword . '%';
			$next                      .= "/keyword:{$keyword}";
		}
		$paginator = new Model([
			'data'  => Product::find($conditions),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page     = $paginator->paginate();
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
		$this->view->next        = $next;
	}

	function createAction() {
		$product               = new Product;
		$user_product          = new UserProduct;
		$user_product->user_id = $this->currentUser->id;
		if ($this->request->isPost()) {
			$product->user_id = $this->currentUser->id;
			$this->_set_model_attributes($product, $user_product);
			if ($product->validation() && $product->create()) {
				$user_product->product_id = $product->id;
				$user_product->create();
				$this->flashSession->success('Penambahan produk berhasil.');
				return $this->response->redirect('/products');
			}
			$this->flashSession->error('Penambahan produk tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($product->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_datas();
		$this->view->menu         = $this->_menu('Products');
		$this->view->product      = $product;
		$this->view->user_product = $user_product;
	}

	function updateAction($id) {
		if (!$product = Product::findFirst(['user_id = ?0 AND id = ?1', 'bind' => [$this->currentUser->id, $id]])) {
			$this->flashSession->error('Produk tidak ditemukan.');
			return $this->dispatcher->forward('products');
		}
		$user_product = UserProduct::findFirst(['user_id = ?0 AND product_id = ?1', 'bind' => [$this->currentUser->id, $id]]);
		$next         = $this->request->get('next');
		if ($this->request->isPost()) {
			$this->_set_model_attributes($product, $user_product);
			if ($product->validation() && $product->update()) {
				$user_product->update();
				$this->flashSession->success('Update produk berhasil.');
				return $this->response->redirect($next);
			}
			$this->flashSession->error('Update produk tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($product->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_datas();
		$this->view->menu         = $this->_menu('Products');
		$this->view->product      = $product;
		$this->view->user_product = $user_product;
		$this->view->next         = $next;
	}

	function publishAction($id) {
		if ($this->request->isPost()) {
			if (!$product = Product::findFirst(['user_id = ?0 AND id = ?1 AND published = 0', 'bind' => [$this->currentUser->id, $id]])) {
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
			if (!$product = Product::findFirst(['user_id = ?0 AND id = ?1 AND published = 1', 'bind' => [$this->currentUser->id, $id]])) {
				$this->flashSession->error('Produk tidak ditemukan.');
			} else {
				$product->update(['published' => 0]);
				$this->flashSession->success('Produk berhasil disembunyikan');
			}
		}
		return $this->response->redirect($this->request->getQuery('next'));
	}

	function deletePictureAction($id) {
		try {
			if (!$this->request->isPost()) {
				throw new Exception('Request tidak valid.');
			}
			if (!$id || !($product = Product::findFirst(['user_id = ?0 AND id = ?1', 'bind' => [$this->currentUser->id, $id]]))) {
				throw new Exception('Produk tidak ditemukan.');
			}
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
			return $this->response->redirect('/products');
		}
		if ($product->picture) {
			$product->deletePicture();
			$this->flashSession->success('Gambar berhasil dihapus');
		}
		return $this->response->redirect("/products/{$product->id}/update?next=" . $this->request->get('next'));
	}

	function deleteAction($id) {
		if ($this->request->isPost()) {
			if (!$product = Product::findFirst(['user_id = ?0 AND id = ?1', 'bind' => [$this->currentUser->id, $id]])) {
				$this->flashSession->error('Produk tidak ditemukan.');
			} else {
				$product->delete();
				$this->flashSession->success('Produk berhasil dihapus');
			}
		}
		return $this->response->redirect('/products');
	}

	private function _prepare_datas() {
		$categories = [];
		$result     = $this->db->query("SELECT a.id, a.name, COUNT(b.id) AS total_products FROM product_categories a LEFT JOIN products b ON a.id = b.product_category_id WHERE a.user_id = {$this->currentUser->id} GROUP BY a.id ORDER BY a.user_id NULLS FIRST, a.name ASC");
		$result->setFetchMode(Enum::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$categories[] = $row;
		}
		$this->view->categories = $categories;
		$this->view->lifetimes  = range(1, 30);
	}

	private function _set_model_attributes(&$product, &$user_product) {
		$product->category = ProductCategory::findFirst(['user_id = ?0 AND id = ?1', 'bind' => [$this->currentUser->id, $this->request->getPost('product_category_id')]]);
		$product->setName($this->request->getPost('name'));
		$product->setStockUnit($this->request->getPost('stock_unit'));
		$product->setDescription($this->request->getPost('description'));
		$product->setLifetime($this->request->getPost('lifetime'));
		$product->setNewPicture($_FILES['picture']);
		$product->setPublished($this->request->getPost('published'));
		$user_product->setPrice($this->request->getPost('price'));
		$user_product->setStock($this->request->getPost('stock'));
		$user_product->setPublished($this->request->getPost('published'));
	}
}