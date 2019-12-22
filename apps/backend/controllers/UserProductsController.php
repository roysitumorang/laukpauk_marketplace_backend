<?php

namespace Application\Backend\Controllers;

use Application\Models\Product;
use Application\Models\ProductCategory;
use Application\Models\UserProduct;
use Application\Models\Role;
use Application\Models\User;
use Phalcon\Paginator\Adapter\QueryBuilder;

class UserProductsController extends ControllerBase {
	private $_user;

	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		if (!($user_id = $this->dispatcher->getParam('user_id', 'int')) ||
			!($this->_user = User::findFirst(['id = ?0 AND role_id = ?1', 'bind' => [$user_id, Role::MERCHANT]]))) {
			$this->flashSession->error('Member tidak ditemukan!');
			$this->response->redirect('/admin/users');
			$this->response->sendHeaders();
		}
	}

	function indexAction() {
		$this->_render(null, $this->dispatcher->getParam('page', 'int') ?: 1);
	}

	function createAction() {
		$user_product = new UserProduct;
		if ($this->request->isPost()) {
			$product_id               = $this->request->getPost('product_id', 'int');
			$user_product->product_id = Product::findFirst(['published = 1 AND id = ?0', 'bind' => [$product_id]])->id;
			$user_product->user_id    = $this->_user->id;
			$user_product->setPrice($this->request->getPost('price'));
			$user_product->setStock($this->request->getPost('stock'));
			if ($user_product->validation() && $user_product->create()) {
				$this->flashSession->success('Penambahan produk berhasil!');
				return $this->response->redirect("/admin/users/{$this->_user->id}/products");
			}
			foreach ($user_product->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_render($user_product);
		$this->view->render('user_products', 'index');
	}

	function updateAction() {
		$page = $this->dispatcher->getParam('page', 'int') ?: 1;
		if ($this->request->isPost()) {
			$input = filter_input_array(INPUT_POST, [
				'id'    => ['filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY],
				'price' => ['filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY],
				'stock' => ['filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY],
			]);
			foreach ($input['id'] as $k => $id) {
				$user_product = UserProduct::findFirst(['user_id = ?0 AND id = ?1', 'bind' => [$this->_user->id, $id]]);
				if ($user_product) {
					$user_product->setPrice(max($input['price'][$k] ?: 0, 0));
					$user_product->setStock(max($input['stock'][$k] ?: 0, 0));
					$user_product->update();
				}
			}
			$this->flashSession->success('Update produk berhasil!');
			return $this->response->redirect("/admin/users/{$this->_user->id}/products" . ($page > 1 ? '/index/page:' . $page : ''));
		}
		$this->_render($user_product, $page);
	}

	function publishAction($id) {
		$user_product = UserProduct::findFirst(['user_id = ?0 AND product_id = ?1 AND published = 0', 'bind' => [$this->_user->id, $id]]);
		if (!$user_product) {
			$this->flashSession->error('Produk tidak ditemukan!');
			return $this->response->redirect("/admin/users/{$this->_user->id}/products");
		}
		$user_product->update(['published' => 1]);
		return $this->response->redirect($this->request->get('next'));
	}

	function unpublishAction($id) {
		$user_product = UserProduct::findFirst(['user_id = ?0 AND product_id = ?1 AND published = 1', 'bind' => [$this->_user->id, $id]]);
		if (!$user_product) {
			$this->flashSession->error('Produk tidak ditemukan!');
			return $this->response->redirect("/admin/users/{$this->_user->id}/products");
		}
		$user_product->update(['published' => 0]);
		return $this->response->redirect($this->request->get('next'));
	}

	function deleteAction($id) {
		if ($this->request->isPost() &&
			($user_product = UserProduct::findFirst(['user_id = ?0 AND product_id = ?1', 'bind' => [$this->_user->id, $id]]))) {
			$user_product->delete();
		}
		return $this->response->redirect($this->request->get('next'));
	}

	private function _render(UserProduct $user_product = null, $current_page = 1) {
		$limit         = $this->config->per_page;
		$offset        = ($current_page - 1) * $limit;
		$user_products = [];
		$categories    = [];
		$products      = [];
		$search_query  = $this->dispatcher->getParam('keyword', 'string') ?: null;
		$builder       = $this->modelsManager->createBuilder()
			->columns([
				'b.id',
				'b.user_id',
				'b.product_id',
				'category' => 'd.name',
				'c.name',
				'c.stock_unit',
				'b.price',
				'b.stock',
				'b.published',
			])
			->from(['a' => 'Application\Models\User'])
			->join('Application\Models\UserProduct', 'a.id = b.user_id', 'b')
			->join('Application\Models\Product', 'b.product_id = c.id', 'c')
			->join('Application\Models\ProductCategory', 'c.product_category_id = d.id', 'd')
			->orderBy('d.name, c.name, c.stock_unit')
			->where('a.id = ' . $this->_user->id);
		if ($search_query) {
			$keywords = preg_split('/ /', $search_query, -1, PREG_SPLIT_NO_EMPTY);
			foreach ($keywords as $keyword) {
				$builder->andWhere("c.name ILIKE '%{$keyword}%'");
			}
		}
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page  = $paginator->paginate();
		$pages = $this->_setPaginationRange($page);
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$user_products[] = $item;
		}
		foreach (ProductCategory::find(['published = 1', 'order' => 'name']) as $category) {
			$category_products = [];
			foreach ($category->getProducts(['published = 1 AND NOT EXISTS(SELECT 1 FROM Application\Models\UserProduct WHERE Application\Models\UserProduct.product_id = Application\Models\Product.id AND Application\Models\UserProduct.user_id = ?0)', 'bind' => [$this->_user->id], 'columns' => 'id, name, stock_unit', 'order' => 'name, stock_unit']) as $product) {
				$category_products[] = $product;
			}
			if ($category_products) {
				$categories[]            = $category;
				$products[$category->id] = $category_products;
			}
		}
		$this->view->menu             = $this->_menu('Members');
		$this->view->user             = $this->_user;
		$this->view->pages            = $pages;
		$this->view->page             = $page;
		$this->view->user_products    = $user_products;
		$this->view->categories       = $categories;
		$this->view->products         = $products;
		$this->view->current_products = $products[$categories[0]->id];
		$this->view->keyword          = $search_query;
		$this->view->next             = $this->request->getServer('REQUEST_URI');
		if ($user_product) {
			$this->view->user_product = $user_product;
		}
	}
}