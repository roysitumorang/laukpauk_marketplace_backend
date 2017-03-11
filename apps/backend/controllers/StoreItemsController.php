<?php

namespace Application\Backend\Controllers;

use Application\Models\Product;
use Application\Models\ProductCategory;
use Application\Models\StoreItem;
use Application\Models\Role;
use Application\Models\User;
use Phalcon\Paginator\Adapter\QueryBuilder;

class StoreItemsController extends ControllerBase {
	private $_user;

	function beforeExecuteRoute() {
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
		$store_item = new StoreItem;
		$page       = $this->request->get('page', 'int') ?: 1;
		if ($this->request->isPost()) {
			$product_id          = $this->request->getPost('product_id', 'int');
			$store_item->product = Product::findFirst(['published = 1 AND id = ?0', 'bind' => [$product_id]]);
			$store_item->user    = $this->_user;
			$store_item->setPrice($this->request->getPost('price'));
			$store_item->setStock($this->request->getPost('stock'));
			$store_item->setOrderClosingHour($this->request->getPost('order_closing_hour'));
			if ($store_item->validation() && $store_item->create()) {
				$this->flashSession->success('Penambahan produk berhasil!');
				return $this->response->redirect("/admin/users/{$this->_user->id}/store_items" . ($page > 1 ? '/index/page:' . $page : ''));
			}
			foreach ($store_item->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_render($store_item, $page);
	}

	function updateAction($id) {
		$store_item = StoreItem::findFirst(['user_id = ?0 AND product_id = ?1', 'bind' => [$this->_user->id, $id]]);
		$page       = $this->request->get('page', 'int') ?: 1;
		if (!$store_item) {
			$this->flashSession->error('Produk tidak ditemukan!');
			return $this->response->redirect("/admin/users/{$this->_user->id}/store_items");
		}
		if ($this->request->isPost()) {
			$store_item->setPrice($this->request->getPost('price'));
			$store_item->setStock($this->request->getPost('stock'));
			if ($store_item->validation() && $store_item->update()) {
				$this->flashSession->success('Update produk berhasil!');
				return $this->response->redirect("/admin/users/{$this->_user->id}/store_items" . ($page > 1 ? '/index/page:' . $page : ''));
			}
			foreach ($store_item->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_render($store_item, $page);
	}

	function publishAction($id) {
		$store_item = StoreItem::findFirst(['user_id = ?0 AND product_id = ?1 AND published = 0', 'bind' => [$this->_user->id, $id]]);
		if (!$store_item) {
			$this->flashSession->error('Produk tidak ditemukan!');
			return $this->response->redirect("/admin/users/{$this->_user->id}/store_items");
		}
		$page = $this->request->get('page', 'int') ?: 1;
		$store_item->update(['published' => 1]);
		return $this->response->redirect("/admin/users/{$this->_user->id}/store_items" . ($page > 1 ? '/index/page:' . $page : ''));
	}

	function unpublishAction($id) {
		$store_item = StoreItem::findFirst(['user_id = ?0 AND product_id = ?1 AND published = 1', 'bind' => [$this->_user->id, $id]]);
		if (!$store_item) {
			$this->flashSession->error('Produk tidak ditemukan!');
			return $this->response->redirect("/admin/users/{$this->_user->id}/store_items");
		}
		$page = $this->request->get('page', 'int') ?: 1;
		$store_item->update(['published' => 0]);
		return $this->response->redirect("/admin/users/{$this->_user->id}/store_items" . ($page > 1 ? '/index/page:' . $page : ''));
	}

	function deleteAction($id) {
		$page = $this->dispatcher->getParam('page', 'int') ?: 1;
		if ($this->request->isPost() &&
			($store_item = StoreItem::findFirst(['user_id = ?0 AND product_id = ?1', 'bind' => [$this->_user->id, $id]]))) {
			$store_item->delete();
		}
		return $this->response->redirect("/admin/users/{$this->_user->id}/store_items" . ($page > 1 ? '/index/page:' . $page : ''));
	}

	private function _render(StoreItem $store_item = null, $current_page = 1) {
		$limit               = $this->config->per_page;
		$offset              = ($current_page - 1) * $limit;
		$store_items         = [];
		$categories          = [];
		$products            = [];
		$order_closing_hours = [];
		$builder             = $this->modelsManager->createBuilder()
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
				'b.order_closing_hour',
			])
			->from(['a' => 'Application\Models\User'])
			->join('Application\Models\StoreItem', 'a.id = b.user_id', 'b')
			->join('Application\Models\Product', 'b.product_id = c.id', 'c')
			->join('Application\Models\ProductCategory', 'c.product_category_id = d.id', 'd')
			->orderBy('d.name, c.name')
			->where('a.id = ' . $this->_user->id);
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page  = $paginator->getPaginate();
		$pages = $this->_setPaginationRange($page);
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			if ($item->order_closing_hour) {
				$item->writeAttribute('order_closing_hour', ($item->order_closing_hour < 10 ? '0' . $item->order_closing_hour : $item->order_closing_hour) . ':00');
			}
			$store_items[] = $item;
		}
		foreach (ProductCategory::find(['published = 1', 'order' => 'name']) as $category) {
			$category_products = [];
			foreach ($category->getProducts(['published = 1 AND NOT EXISTS(SELECT 1 FROM Application\Models\StoreItem WHERE Application\Models\StoreItem.product_id = Application\Models\Product.id AND Application\Models\StoreItem.user_id = ?0)', 'bind' => [$this->_user->id], 'columns' => 'id, name, stock_unit', 'order' => 'name']) as $product) {
				$category_products[] = $product;
			}
			if ($category_products) {
				$categories[]            = $category;
				$products[$category->id] = $category_products;
			}
		}
		foreach (range(User::BUSINESS_HOURS['opening'], User::BUSINESS_HOURS['closing']) as $hour) {
			$order_closing_hours[$hour] = ($hour < 10 ? '0' . $hour : $hour) . ':00';
		}
		$this->view->menu                = $this->_menu('Members');
		$this->view->user                = $this->_user;
		$this->view->pages               = $pages;
		$this->view->page                = $paginator->getPaginate();
		$this->view->store_items         = $store_items;
		$this->view->categories          = $categories;
		$this->view->products            = $products;
		$this->view->current_products    = $products[$categories[0]->id];
		$this->view->order_closing_hours = $order_closing_hours;
		if ($store_item) {
			$this->view->store_item = $store_item;
		}
	}
}