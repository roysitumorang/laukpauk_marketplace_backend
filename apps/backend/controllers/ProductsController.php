<?php

namespace Application\Backend\Controllers;

use Application\Models\{Product, ProductCategory, User, UserProduct};
use Phalcon\Db\Enum;
use Phalcon\Paginator\Adapter\QueryBuilder;

class ProductsController extends ControllerBase {
	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		$this->view->menu = $this->_menu('Products');
	}

	function indexAction() {
		$products     = [];
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int', 1);
		$offset       = ($current_page - 1) * $limit;
		$category_id  = $this->dispatcher->getParam('category_id', 'int');
		$published    = $this->dispatcher->getParam('published', 'int');
		$keyword      = $this->dispatcher->getParam('keyword', 'string');
		$next         = '/admin/products/index';
		$builder      = $this->modelsManager->createBuilder()
				->from(Product::class)
				->orderBy('id DESC');
		$this->_prepareDatas();
		if ($category_id) {
			$builder->andWhere('product_category_id = :category_id:', ['category_id' => $category_id]);
			$next .= "/category_id={$category_id}";
		}
		if (ctype_digit($published)) {
			$builder->andWhere('published = :published:', ['published' => $published]);
			$next .= "/published={$published}";
		}
		if ($keyword) {
			if (ctype_digit($keyword)) {
				$builder->andWhere('id = :id:', ['id' => $keyword]);
			} else if ($keyword) {
				$builder->andWhere('name LIKE :name:', ['name' => '%' . $keyword . '%']);
			}
			$next .= "/keyword={$keyword}";
		}
		$pagination = (new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]))->paginate();
		foreach ($pagination->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$products[] = $item;
		}
		$this->view->setVars([
			'pagination'  => $pagination,
			'pages'       => $this->_setPaginationRange($pagination),
			'products'    => $products,
			'keyword'     => $keyword,
			'category_id' => $category_id,
			'published'   => $published,
			'next'        => $next,
		]);
	}

	function createAction() {
		$product = new Product;
		if ($this->request->isPost()) {
			$this->_assignModelAttributes($product);
			if ($product->validation() && $product->create()) {
				$this->flashSession->success('Penambahan produk berhasil.');
				return $this->response->redirect('/admin/products');
			}
			$this->flashSession->error('Penambahan produk tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($product->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepareDatas();
		$this->view->setVars([
			'product'  => $product,
			'pictures' => [],
		]);
	}

	function updateAction($id) {
		if (!$product = Product::findFirst($id)) {
			$this->flashSession->error('Produk tidak ditemukan.');
			return $this->dispatcher->forward('products');
		}
		$next = $this->request->get('next');
		if ($this->request->isPost()) {
			$this->_assignModelAttributes($product);
			if ($product->validation() && $product->update()) {
				$this->flashSession->success('Update produk berhasil.');
				return $this->response->redirect($next);
			}
			$this->flashSession->error('Update produk tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($product->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepareDatas();
		$this->view->setVars([
			'product'    => $product,
			'active_tab' => 'product',
			'next'       => $next,
		]);
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
				$this->flashSession->error('Produk tidak ditemukan.');
			} else {
				$product->deletePicture();
				$this->flashSession->success('Gambar berhasil dihapus');
			}
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
		$users        = [];
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int', 1);
		$offset       = ($current_page - 1) * $limit;
		$keyword      = $this->dispatcher->getParam('keyword', 'string');
		$builder      = $this->modelsManager->createBuilder()
			->columns(['c.company'])
			->from(['a' => Product::class])
			->join(UserProduct::class, 'a.id = b.product_id', 'b')
			->join(User::class, 'b.user_id = c.id', 'c')
			->where('a.id = ' . $product->id)
			->andWhere('c.status = 1')
			->orderBy('c.company');
		$pagination = (new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]))->paginate();
		foreach ($pagination->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$users[] = $item;
		}
		$this->view->setVars([
			'pagination' => $pagination,
			'pages'      => $this->_setPaginationRange($pagination),
			'product'    => $product,
			'users'      => $users,
			'keyword'    => $keyword,
			'active_tab' => 'merchants',
		]);
	}

	private function _prepareDatas() {
		$categories = [];
		$result     = $this->db->query('SELECT a.id, a.name, COUNT(b.id) AS total_products FROM product_categories a LEFT JOIN products b ON a.id = b.product_category_id GROUP BY a.id ORDER BY a.user_id NULLS FIRST, a.name ASC');
		$result->setFetchMode(Enum::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$categories[$row->id] = $row->name . ' (' . $row->total_products . ')';
		}
		$this->view->categories = $categories;
	}

	private function _assignModelAttributes(Product &$product) {
		if ($product_category_id = $this->request->getPost('product_category_id')) {
			$product->category = ProductCategory::findFirst($product_category_id);
		}
		$product->assign($this->request->getPost(), null, [
			'name',
			'stock_unit',
			'description',
			'published',
		]);
		if ($this->request->hasFiles()) {
			$product->setNewPicture(current(array_filter($this->request->getUploadedFiles(), function(&$v, $k) {
				return $v->getKey() == 'new_picture';
			}, ARRAY_FILTER_USE_BOTH)));
		}
	}
}