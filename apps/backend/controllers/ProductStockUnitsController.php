<?php

namespace Application\Backend\Controllers;

use Application\Models\Product;
use Application\Models\ProductStockUnit;
use Exception;

class ProductStockUnitsController extends ControllerBase {
	private $_product;

	function onConstruct() {
		try {
			if (!($product_id = $this->dispatcher->getParam('product_id', 'int')) || !($this->_product = Product::findFirst($product_id))) {
				throw new Exception('Produk tidak ditemukan');
			}
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
			return $this->response->redirect('/admin/products');
		}
	}

	function indexAction() {
		$this->_prepare();
	}

	function createAction() {
		$stock_unit = new ProductStockUnit;
		if ($this->request->isPost()) {
			$stock_unit->product = $this->_product;
			$stock_unit->name    = $this->request->getPost('name', ['string', 'trim']);
			if ($stock_unit->validation() && $stock_unit->create()) {
				$this->flashSession->success('Penambahan satuan berhasil');
				return $this->response->redirect("/admin/product_stock_units/index/product_id:{$this->_product->id}");
			}
			foreach ($stock_unit->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->stock_unit = $stock_unit;
		$this->_prepare();
	}

	function updateAction($id) {
		$stock_unit = $this->_product->getRelated('stock_units', ['id = :id:', 'bind' => ['id' => $id]])->getFirst();
		if (!$stock_unit) {
			$this->flashSession->error('Satuan tidak ditemukan');
			return $this->response->redirect("/admin/product_stock_units/index/product_id:{$this->_product->id}");
		}
		if ($this->request->isPost()) {
			$stock_unit->product = $this->_product;
			$stock_unit->name    = $this->request->getPost('name', ['string', 'trim']);
			if ($stock_unit->validation() && $stock_unit->update()) {
				$this->flashSession->success('Penambahan satuan berhasil');
				return $this->response->redirect("/admin/product_stock_units/index/product_id:{$this->_product->id}");
			}
			foreach ($stock_unit->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->stock_unit = $stock_unit;
		$this->_prepare();
	}

	function deleteAction($id) {
		try {
			if (!$this->request->isPost()) {
				throw new Exception('Request tidak valid');
			}
			$stock_unit = $this->_product->getRelated('stock_units', ['id = :id:', 'bind' => ['id' => $id]])->getFirst();
			if (!$stock_unit) {
				throw new Exception('Satuan tidak ditemukan');
			}
			$stock_unit->delete();
			$this->flashSession->success('Satuan berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		}
		return $this->response->redirect("/admin/product_stock_units/index/product_id:{$this->_product->id}");
	}

	private function _prepare() {
		$stock_units = [];
		$i           = 0;
		foreach ($this->_product->stock_units as $stock_unit) {
			$stock_unit->writeAttribute('rank', ++$i);
			$stock_units[] = $stock_unit;
		}
		$this->view->menu        = $this->_menu('Products');
		$this->view->product     = $this->_product;
		$this->view->stock_units = $stock_units;
	}
}
