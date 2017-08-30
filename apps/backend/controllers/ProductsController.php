<?php

namespace Application\Backend\Controllers;

use Application\Models\Product;
use Application\Models\ProductCategory;
use Ds\Set;
use Exception;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\Model;
use Phalcon\Paginator\Adapter\QueryBuilder;

class ProductsController extends ControllerBase {
	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$category_id  = $this->dispatcher->getParam('category_id', 'int');
		$published    = filter_var($this->dispatcher->getParam('published'), FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
		$keyword      = $this->dispatcher->getParam('keyword', 'string');
		$next         = '/admin/products/index';
		$conditions   = ['', 'bind' => [], 'order' => 'id DESC'];
		$this->_prepare_datas();
		if ($category_id) {
			$conditions[0]                    .= ($conditions[0] ? ' AND' : '') . ' product_category_id = :category_id:';
			$conditions['bind']['category_id'] = $category_id;
			$next                             .= "/category_id:{$category_id}";
		}
		if (is_int($published)) {
			$conditions[0]                  .= ($conditions[0] ? ' AND' : '') . ' published = :published:';
			$conditions['bind']['published'] = $published;
			$next                           .= "/published:{$published}";
		}
		if ($keyword) {
			if (filter_var($keyword, FILTER_VALIDATE_INT)) {
				$conditions[0]           .= ($conditions[0] ? ' AND' : '') . ' id = :id:';
				$conditions['bind']['id'] = $keyword;
			} else if ($keyword) {
				$conditions[0]             .= ($conditions[0] ? ' AND' : '') . ' name LIKE :name:';
				$conditions['bind']['name'] = '%' . $keyword . '%';
			}
			$next .= "/keyword:{$keyword}";
		}
		$paginator = new Model([
			'data'  => Product::find($conditions),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page     = $paginator->getPaginate();
		$pages    = $this->_setPaginationRange($page);
		$products = new Set;
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$products->add($item);
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

	function toggleStatusAction($id) {
		if ($this->request->isPost()) {
			if (!$product = Product::findFirst($id)) {
				$this->flashSession->error('Produk tidak ditemukan.');
			} else {
				$product->update(['published' => $product->published ? 0 : 1]);
			}
		}
		return $this->response->redirect($this->request->getQuery('next'));
	}

	function deletePictureAction($id) {
		if ($this->request->isPost()) {
			if (!$product = Product::findFirst(['id = ?0 AND picture IS NOT NULL', 'bind' => [$id]])) {
				throw new Exception('Produk tidak ditemukan.');
			}
			$product->deletePicture();
			$this->flashSession->success('Gambar berhasil dihapus');
		}
		return $this->response->redirect("/admin/products/{$product->id}/update?next=" . $this->request->get('next'));
	}

	function deleteAction($id) {
		if ($this->request->isPost()) {
			if (!$product = Product::findFirst($id)) {
				$this->flashSession->error('Produk tidak ditemukan.');
			} else {
				$product->delete();
				$this->flashSession->success('Produk berhasil dihapus');
			}
		}
		return $this->response->redirect('/admin/products');
	}

	function merchantsAction($id) {
		if (!$product = Product::findFirst($id)) {
			$this->flashSession->error('Produk tidak ditemukan.');
			return $this->dispatcher->forward('products');
		}
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$keyword      = $this->dispatcher->getParam('keyword', 'string');
		$builder      = $this->modelsManager->createBuilder()
			->columns(['c.company'])
			->from(['a' => 'Application\Models\Product'])
			->join('Application\Models\UserProduct', 'a.id = b.product_id', 'b')
			->join('Application\Models\User', 'b.user_id = c.id', 'c')
			->where('a.id = ' . $product->id)
			->andWhere('c.status = 1')
			->orderBy('c.company');
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page  = $paginator->getPaginate();
		$pages = $this->_setPaginationRange($page);
		$users = new Set;
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$users->add($item);
		}
		$this->view->menu       = $this->_menu('Products');
		$this->view->page       = $page;
		$this->view->pages      = $pages;
		$this->view->product    = $product;
		$this->view->users      = $users;
		$this->view->keyword    = $keyword;
		$this->view->active_tab = 'merchants';
	}

	private function _prepare_datas() {
		$categories = new Set;
		$result     = $this->db->query('SELECT a.id, a.name, COUNT(b.id) AS total_products FROM product_categories a LEFT JOIN products b ON a.id = b.product_category_id GROUP BY a.id ORDER BY a.user_id NULLS FIRST, a.name ASC');
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$categories->add($row);
		}
		$this->view->categories = $categories;
	}

	private function _set_model_attributes(&$product) {
		$product->category = ProductCategory::findFirst($this->request->getPost('product_category_id'));
		$product->setName($this->request->getPost('name'));
		$product->setStockUnit($this->request->getPost('stock_unit'));
		$product->setDescription($this->request->getPost('description'));
		$product->setNewPicture($_FILES['picture']);
		$product->setPublished($this->request->getPost('published'));
	}
}