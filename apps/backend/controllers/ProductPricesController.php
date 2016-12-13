<?php

namespace Application\Backend\Controllers;

use Application\Models\Product;
use Application\Models\ProductCategory;
use Application\Models\ProductPrice;
use Application\Models\User;
use Exception;
use Phalcon\Db;

class ProductPricesController extends ControllerBase {
	private $_user;

	function onConstruct() {
		try {
			if (!($user_id = $this->dispatcher->getParam('user_id', 'int')) || !($this->_user = User::findFirst($user_id))) {
				throw new Exception('Data tidak ditemukan');
			}
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
			return $this->response->redirect('/admin/users');
		}
	}

	function indexAction() {
		$prices = [];
		$result = $this->db->query("SELECT a.id, a.product_id, b.name AS product, c.name AS category, a.value, a.unit_size, b.stock_unit, a.published, a.order_closing_hour FROM product_prices a JOIN products b ON a.product_id = b.id JOIN product_categories c ON b.product_category_id = c.id WHERE a.user_id = {$this->_user->id} ORDER BY CONCAT(c.name, b.name)");
		$result->setFetchMode(Db::FETCH_OBJ);
		$i      = 0;
		while ($price = $result->fetch()) {
			$price->rank = ++$i;
			$prices[]    = $price;
		}
		$this->view->prices = $prices;
		$this->_prepare_form_datas();
	}

	function createAction() {
		$price = new ProductPrice;
		if ($this->request->isPost()) {
			$product_id                = $this->request->getPost('product_id', 'int');
			$price->product            = Product::findFirst(['published = 1 AND id = :id:', 'bind' => ['id' => $product_id]]);
			$price->value              = $this->request->getPost('value', 'int');
			$price->unit_size          = $this->request->getPost('unit_size', 'float');
			$price->order_closing_hour = $this->request->getPost('order_closing_hour');
			$price->user               = $this->_user;
			if ($price->validation() && $price->create()) {
				$this->flashSession->success('Penambahan produk berhasil');
				return $this->response->redirect("/admin/product_prices/index/user_id:{$this->_user->id}");

			}
			foreach ($price->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->price = $price;
		$this->_prepare_form_datas();
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
				$product_id                = $this->request->getPost('product_id', 'int');
				$price->product            = Product::findFirst(['published = 1 AND id = :id:', 'bind' => ['id' => $product_id]]);
				$price->value              = $this->request->getPost('value', 'int');
				$price->unit_size          = $this->request->getPost('unit_size', 'float');
				$price->order_closing_hour = $this->request->getPost('order_closing_hour');
				$price->user               = $this->_user;
			}
			if ($price->validation() && $price->update()) {
				return $this->response->redirect("/admin/product_prices/index/user_id:{$this->_user->id}");
			}
			foreach ($price->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->price = $price;
		$this->_prepare_form_datas();
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

	private function _prepare_form_datas() {
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
		$this->view->sizes            = ProductPrice::SIZES;
		$this->view->categories       = $categories;
		$this->view->current_products = $products[$categories[0]->id];
		$this->view->products_json    = json_encode($products, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
	}
}