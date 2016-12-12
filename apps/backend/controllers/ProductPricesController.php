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
		$categories = [];
		$products   = [];
		$prices     = [];
		foreach (ProductCategory::find(['published = 1', 'order' => 'name']) as $category) {
			$category_products = [];
			foreach ($category->getProducts(['published = 1', 'columns' => 'id, name, stock_unit', 'order' => 'name']) as $product) {
				$category_products[] = $product;
			}
			$categories[]            = $category;
			$products[$category->id] = $category_products;
		}
		$result = $this->db->query("SELECT a.id, a.product_id, b.name AS product, c.name AS category, a.value, a.unit_size, b.stock_unit, a.published, a.order_closing_hour FROM product_prices a JOIN products b ON a.product_id = b.id JOIN product_categories c ON b.product_category_id = c.id WHERE a.user_id = {$this->_user->id} ORDER BY CONCAT(c.name, b.name)");
		$result->setFetchMode(Db::FETCH_OBJ);
		$i      = 0;
		while ($price = $result->fetch()) {
			$price->rank = ++$i;
			$prices[]    = $price;
		}
		$this->view->menu             = $this->_menu('Members');
		$this->view->user             = $this->_user;
		$this->view->prices           = $prices;
		$this->view->sizes            = ProductPrice::SIZES;
		$this->view->categories       = $categories;
		$this->view->current_products = $products[$categories[0]->id];
		$this->view->products_json    = json_encode($products, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
	}

	function createAction() {
		try {
			if (!$this->request->isPost()) {
				throw new Exception('Request tidak valid');
			}
			$product_id         = $this->request->getPost('product_id', 'int');
			$value              = $this->request->getPost('value', 'int');
			$unit_size          = $this->request->getPost('unit_size', 'float');
			$order_closing_hour = $this->request->getPost('order_closing_hour');
			if (!isset($value[0])) {
				throw new Exception('Harga harus diisi dengan angka bulat');
			}
			if ($product_id && ($product = Product::findFirst(['published = 1 AND id = :id:', 'bind' => ['id' => $product_id]])) && !$this->_user->getRelated('product_prices', ['id = :id:', 'bind' => ['id' => $product->id]])->getFirst() && array_key_exists($unit_size, ProductPrice::SIZES)) {
				$price                     = new ProductPrice;
				$price->product            = $product;
				$price->value              = $value;
				$price->unit_size          = $unit_size;
				$price->order_closing_hour = $order_closing_hour;
				$price->user               = $this->_user;
				if (!$price->create()) {
					foreach ($price->getMessages() as $error) {
						$this->flashSession->error($error);
					}
				} else {
					$this->flashSession->success('Penambahan produk berhasil');
				}
			}
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		}
		return $this->response->redirect("/admin/product_prices/index/user_id:{$this->_user->id}");
	}

	function updateAction($id) {
		try {
			if (!$this->request->isPost()) {
				throw new Exception('Request tidak valid');
			}
			$price = $this->_user->getRelated('product_prices', ['id = :id:', 'bind' => ['id' => $id]])->getFirst();
			if (!$price) {
				throw new Exception('Data tidak ditemukan');
			}
			$price->writeAttribute('published', $price->published ? 0 : 1);
			$price->update();
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		}
		return $this->response->redirect("/admin/product_prices/index/user_id:{$this->_user->id}");
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
}