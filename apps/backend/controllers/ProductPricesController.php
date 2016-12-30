<?php

namespace Application\Backend\Controllers;

use Application\Models\Product;
use Application\Models\ProductCategory;
use Application\Models\ProductPrice;
use Application\Models\Role;
use Application\Models\User;
use Exception;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class ProductPricesController extends ControllerBase {
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
				'category'              => 'd.name',
				'c.name',
				'c.stock_unit',
				'b.value',
				'b.published',
				'b.order_closing_hour',
			])
			->from(['a' => 'Application\Models\User'])
			->join('Application\Models\ProductPrice', 'a.id = b.user_id', 'b')
			->join('Application\Models\Product', 'b.product_id = c.id', 'c')
			->join('Application\Models\ProductCategory', 'c.product_category_id = d.id', 'd')
			->orderBy('d.name, c.name')
			->where('a.id = ' . $this->_user->id);
		$paginator = new PaginatorQueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page   = $paginator->getPaginate();
		$pages  = $this->_setPaginationRange($page);
		$prices = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$prices[] = $item;
		}
		$this->view->prices = $prices;
		$this->view->user   = $this->_user;
		$this->view->pages  = $pages;
		$this->view->page   = $paginator->getPaginate();
		$this->_prepare_form_datas();
	}

	function createAction() {
		$price = new ProductPrice;
		if ($this->request->isPost()) {
			$product_id     = $this->request->getPost('product_id', 'int');
			$price->product = Product::findFirst(['published = 1 AND id = ?0', 'bind' => [$product_id]]);
			$price->setValue($this->request->getPost('value'));
			$price->setOrderClosingHour($this->request->getPost('order_closing_hour'));
			$price->user    = $this->_user;
			if ($price->validation() && $price->create()) {
				$this->flashSession->success('Penambahan produk berhasil');
				return $this->response->redirect("/admin/product_prices/index/user_id:{$this->_user->id}");

			}
			foreach ($price->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_form_datas($price);
	}

	function updateAction($id) {
		$price = $this->_user->getRelated('product_prices', ['id = :id:', 'bind' => ['id' => $id]])->getFirst();
		if (!$price) {
			$this->flashSession->error('Data tidak ditemukan');
			return $this->response->redirect("/admin/product_prices/index/user_id:{$this->_user->id}");
		}
		if ($this->request->isPost()) {
			if ($this->dispatcher->getParam('published')) {
				$price->writeAttribute('published', $price->published ? 0 : 1);
			} else {
				$price->setValue($this->request->getPost('value'));
				$price->setOrderClosingHour($this->request->getPost('order_closing_hour'));
			}
			if ($price->validation() && $price->update()) {
				return $this->response->redirect("/admin/product_prices/index/user_id:{$this->_user->id}");
			}
			foreach ($price->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_form_datas($price);
	}

	function deleteAction($id) {
		try {
			if (!$this->request->isPost()) {
				throw new Exception('Request tidak valid');
			}
			$price = $this->_user->getRelated('product_prices', ['id = :id:', 'bind' => ['id' => $id]])->getFirst();
			if (!$price) {
				throw new Exception('Data tidak ditemukan');
			}
			$price->delete();
			$this->flashSession->success('Produk berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		}
		return $this->response->redirect("/admin/product_prices/index/user_id:{$this->_user->id}");
	}

	private function _prepare_form_datas(ProductPrice $price = null) {
		$categories = [];
		$products   = [];
		foreach (ProductCategory::find(['published = 1', 'order' => 'name']) as $category) {
			$category_products = [];
			foreach ($category->getProducts(['published = 1', 'columns' => 'id, name, stock_unit', 'order' => 'name']) as $product) {
				$category_products[] = $product;
			}
			$categories[]            = $category;
			$products[$category->id] = $category_products;
		}
		$this->view->menu             = $this->_menu('Members');
		$this->view->user             = $this->_user;
		$this->view->categories       = $categories;
		$this->view->current_products = $products[$categories[0]->id];
		$this->view->products_json    = json_encode($products, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		if ($price) {
			$this->view->price = $price;
		}
	}
}