<?php

namespace Application\Backend\Controllers;

use Application\Models\Product;
use Application\Models\ProductCategory;
use Phalcon\Db;
use Phalcon\Exception;
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
		$parameter    = [];
		$conditions   = [[]];
		$this->_prepare_datas();
		if ($category_id) {
			$conditions[0][]           = 'product_category_id = :category_id:';
			$conditions['category_id'] = $category_id;
			$next                     .= "/category_id:{$category_id}";
		}
		if (is_int($published)) {
			$conditions[0][]         = 'published = :published:';
			$conditions['published'] = $published;
			$next                   .= "/published:{$published}";
		}
		if (filter_var($keyword, FILTER_VALIDATE_INT)) {
			$conditions[0][]  = 'id = :id:';
			$conditions['id'] = $keyword;
			$next            .= "/keyword:{$keyword}";
		} else if ($keyword) {
			$conditions[0][]    = 'name LIKE :name:';
			$conditions['name'] = '%' . $keyword . '%';
			$next              .= "/keyword:{$keyword}";
		}
		if ($conditions[0]) {
			$parameter['conditions'] = implode(' AND ', array_shift($conditions));
			$parameter['bind']       = $conditions;
		}
		$paginator = new Model([
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

	function deletePictureAction($id) {
		try {
			if (!$this->request->isPost()) {
				throw new Exception('Request tidak valid.');
			}
			if (!$id || !($product = Product::findFirstById($id))) {
				throw new Exception('Produk tidak ditemukan.');
			}
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
			return $this->response->redirect('/admin/products');
		}
		if ($product->picture) {
			$product->deletePicture();
			$this->flashSession->success('Gambar berhasil dihapus');
		}
		return $this->response->redirect("/admin/products/{$product->id}/update?next=" . $this->request->get('next'));
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

	function merchantsAction($id) {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$keyword      = $this->dispatcher->getParam('keyword', 'string');
		$product      = Product::findFirst($id);
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
		$users = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$users[] = $item;
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
		$categories = [];
		$result     = $this->db->query('SELECT a.id, a.name, COUNT(b.id) AS total_products FROM product_categories a LEFT JOIN products b ON a.id = b.product_category_id GROUP BY a.id ORDER BY a.user_id NULLS FIRST, a.name ASC');
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$categories[] = $row;
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
		$product->setNewPicture($_FILES['picture']);
		$product->setPublished($this->request->getPost('published'));
	}
}