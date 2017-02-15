<?php

namespace Application\Backend\Controllers;

use Application\Models\Product;
use Application\Models\ProductCategory;
use Application\Models\StoreItem;
use Application\Models\Role;
use Application\Models\User;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class StoreItemsController extends ControllerBase {
	private $_user;

	function onConstruct() {
		if (!$this->_user = User::findFirst(['id = ?0 AND role_id = ?1', 'bind' => [
			$this->dispatcher->getParam('user_id', 'int'),
			Role::MERCHANT,
		]])) {
			$this->flashSession->error('Data tidak ditemukan');
			$this->response->redirect('admin/users');
			$this->response->send();
			return false;
		}
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$builder      = $this->modelsManager->createBuilder()
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
		$paginator = new PaginatorQueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page        = $paginator->getPaginate();
		$pages       = $this->_setPaginationRange($page);
		$store_items = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			if ($item->order_closing_hour) {
				$item->writeAttribute('order_closing_hour', ($item->order_closing_hour < 10 ? '0' . $item->order_closing_hour : $item->order_closing_hour) . ':00');
			}
			$store_items[] = $item;
		}
		$this->view->store_items = $store_items;
		$this->view->user        = $this->_user;
		$this->view->pages       = $pages;
		$this->view->page        = $paginator->getPaginate();
		$this->view->menu        = $this->_menu('Members');
	}

	function createAction() {
		$store_item = new StoreItem;
		if ($this->request->isPost()) {
			$product_id          = $this->request->getPost('product_id', 'int');
			$store_item->product = Product::findFirst(['published = 1 AND id = ?0', 'bind' => [$product_id]]);
			$store_item->user    = $this->_user;
			$store_item->setPrice($this->request->getPost('price'));
			$store_item->setStock($this->request->getPost('stock'));
			$store_item->setOrderClosingHour($this->request->getPost('order_closing_hour'));
			if ($store_item->validation() && $store_item->create()) {
				$this->response->setJsonContent(['status' => 1], JSON_UNESCAPED_SLASHES);
				return $this->response;
			}
			foreach ($store_item->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_render_form($store_item);
	}

	function updateAction($id) {
		$store_item = StoreItem::findFirst(['user_id = ?0 AND id = ?1', 'bind' => [$this->_user->id, $id]]);
		if (!$store_item) {
			$this->flashSession->error('Produk tidak ditemukan');
			return $this->response->redirect("/admin/store_items/index/user_id:{$this->_user->id}");
		}
		if ($this->request->isPost()) {
			if ($this->dispatcher->getParam('published')) {
				$store_item->writeAttribute('published', $store_item->published ? 0 : 1);
			} else {
				$store_item->setPrice($this->request->getPost('price'));
				$store_item->setStock($this->request->getPost('stock'));
				$store_item->setOrderClosingHour($this->request->getPost('order_closing_hour'));
			}
			if ($store_item->validation() && $store_item->update()) {
				$this->response->setJsonContent(['status' => 1], JSON_UNESCAPED_SLASHES);
				return $this->response;
			}
			foreach ($store_item->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_render_form($store_item);
	}

	function deleteAction($id) {
		if ($this->request->isPost()) {
			$store_item = StoreItem::findFirst(['user_id = ?0 AND id = ?1', 'bind' => [$this->_user->id, $id]]);
			$store_item && $store_item->delete();
		}
	}

	private function _render_form(StoreItem $store_item = null) {
		$categories          = [];
		$products            = [];
		$order_closing_hours = [];
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
		$this->view->user                = $this->_user;
		$this->view->categories          = $categories;
		$this->view->current_products    = $products[$categories[0]->id];
		$this->view->products_json       = json_encode($products, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		$this->view->order_closing_hours = $order_closing_hours;
		if ($store_item) {
			$this->view->store_item = $store_item;
		}
		$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
		$this->view->start();
		$this->view->finish();
		$response = [
			'status' => -1,
			'data'   => str_replace(["\n", "\t"], '', $this->view->getContent()),
		];
		$this->response->setJsonContent($response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}