<?php

namespace Application\Backend\Controllers;

use Application\Models\Product;
use Application\Models\ProductAccessor;
use Application\Models\Role;
use Application\Models\User;
use Phalcon\Paginator\Adapter\Model;

class ProductAccessorsController extends ControllerBase {
	private $_product, $_limit, $current_page, $_offset;

	function beforeExecuteRoute() {
		if (!($product_id = $this->dispatcher->getParam('product_id', 'int')) || !($this->_product = Product::findFirstById($product_id))) {
			$this->flashSession->error('Produk tidak ditemukan!');
			$this->response->redirect('/admin/products');
			$this->response->sendHeaders();
		}
	}

	function initialize() {
		parent::initialize();
		$this->_limit           = 2;//$this->config->per_page;
		$this->_current_page    = $this->dispatcher->getParam('page', 'int') ?: 1;
		$this->_offset          = ($this->_current_page - 1) * $this->_limit;
		$this->view->menu       = $this->_menu('Products');
		$this->view->active_tab = 'accessors';
		$this->view->product    = $this->_product;
	}

	function indexAction() {
		$product_accessors = [];
		$paginator         = new Model([
			'data'  => $this->_product->getRelated('accessors', ['order' => 'name']),
			'limit' => $this->_limit,
			'page'  => $this->_current_page,
		]);
		$page  = $paginator->getPaginate();
		$pages = $this->_setPaginationRange($page);
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$this->_offset);
			$product_accessors[] = $item;
		}
		$this->view->product_accessors = $product_accessors;
		$this->view->page              = $page;
		$this->view->pages             = $pages;
	}

	function createAction() {
		$product_accessor = new ProductAccessor;
		if ($this->request->isPost() &&
			($user_id = $this->request->getPost('user_id', 'int')) &&
			($user = User::findFirst(['id = ?0 AND role_id = ?1 AND status = 1', 'bind' => [$user_id, Role::MERCHANT]])) &&
			!$this->_product->getRelated('accessors', ['user_id = ?0', 'bind' => [$user->id]])->getFirst()) {
			$product_accessor->product = $this->_product;
			$product_accessor->user    = $user;
			if ($product_accessor->validation() && $product_accessor->create()) {
				$page = $this->dispatcher->getParam('page', 'int') ?: 1;
				$this->flashSession->success('Penambahan merchant berhasil!');
				return $this->response->redirect('/admin/product_accessors/index/product_id:' . $this->_product->id . ($page > 1 ? '/index/page:' . $page : ''));
			}
			foreach ($product_accessor->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$nominations = [];
		$paginator   = new Model([
			'data'  => User::find(['role_id = ?0 AND status = 1 AND NOT EXISTS(SELECT 1 FROM Application\Models\ProductAccessor WHERE Application\Models\ProductAccessor.user_id = Application\Models\User.id AND Application\Models\ProductAccessor.product_id = ?1)', 'bind' => [Role::MERCHANT, $this->_product->id], 'columns' => 'id, name, mobile_phone', 'order' => 'name']),
			'limit' => $this->_limit,
			'page'  => $this->_current_page,
		]);
		$page  = $paginator->getPaginate();
		$pages = $this->_setPaginationRange($page);
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$this->_offset);
			$nominations[] = $item;
		}
		$this->view->nominations      = $nominations;
		$this->view->page             = $page;
		$this->view->pages            = $pages;
		$this->view->product_accessor = $product_accessor;
	}

	function deleteAction($id) {
		$product_accessor = ProductAccessor::findFirst(['product_id = ?0 AND user_id = ?1', 'bind' => [$this->_product->id, $id]]);
		if (!$product_accessor) {
			$this->flashSession->error('Merchant tidak ditemukan!');
		} else if ($this->request->isPost()) {
			$page = $this->dispatcher->getParam('page', 'int') ?: 1;
			if ($product_accessor->delete()) {
				$this->flashSession->success('Merchant berhasil dihapus!');
			}
		}
		return $this->response->redirect('/admin/product_accessors/index/product_id:' . $this->_product->id . ($page > 1 ? '/index/page:' . $page : ''));
	}
}
