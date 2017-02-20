<?php

namespace Application\Backend\Controllers;

use Application\Models\Product;
use Application\Models\ProductLink;
use Phalcon\Paginator\Adapter\Model;

class ProductLinksController extends ControllerBase {
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
		$this->_limit           = $this->config->per_page;
		$this->_current_page    = $this->dispatcher->getParam('page', 'int') ?: 1;
		$this->_offset          = ($this->_current_page - 1) * $this->_limit;
		$this->view->menu       = $this->_menu('Products');
		$this->view->active_tab = 'linked_products';
		$this->view->product    = $this->_product;
	}

	function indexAction() {
		$linked_products = [];
		$paginator = new Model([
			'data'  => $this->_product->getRelated('linked_products', ['order' => 'name']),
			'limit' => $this->_limit,
			'page'  => $this->_current_page,
		]);
		$page  = $paginator->getPaginate();
		$pages = $this->_setPaginationRange($page);
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$this->_offset);
			$linked_products[] = $item;
		}
		$this->view->linked_products = $linked_products;
		$this->view->page            = $page;
		$this->view->pages           = $pages;
	}

	function createAction() {
		$link = new ProductLink;
		if ($this->request->isPost() &&
			($linked_product_id = $this->request->getPost('linked_product_id', 'int')) &&
			$linked_product_id != $this->_product->id &&
			($linked_product = Product::findFirst(['id = ?0 AND published = 1', 'bind' => [$linked_product_id]])) &&
			!$this->_product->getRelated('linked_products', ['linked_product_id = ?0', 'bind' => [$linked_product->id]])->getFirst()) {
			$link->product        = $this->_product;
			$link->linked_product = $linked_product;
			if ($link->validation() && $link->create()) {
				$page = $this->dispatcher->getParam('page', 'int') ?: 1;
				$this->flashSession->success('Penambahan produk terkait berhasil!');
				return $this->response->redirect('/admin/product_links/index/product_id:' . $this->_product->id . ($page > 1 ? '/index/page:' . $page : ''));
			}
			foreach ($link->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$nominations = [];
		$paginator   = new Model([
			'data'  => Product::find(['id != ?0 AND published = 1 AND NOT EXISTS(SELECT 1 FROM Application\Models\ProductLink WHERE Application\Models\ProductLink.linked_product_id = Application\Models\Product.id AND Application\Models\ProductLink.product_id = ?1)', 'bind' => [$this->_product->id, $this->_product->id], 'order' => 'name']),
			'limit' => $this->_limit,
			'page'  => $this->_current_page,
		]);
		$page  = $paginator->getPaginate();
		$pages = $this->_setPaginationRange($page);
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$this->_offset);
			$nominations[] = $item;
		}
		$this->view->nominations = $nominations;
		$this->view->page        = $page;
		$this->view->pages       = $pages;
	}

	function deleteAction($id) {
		$link = ProductLink::findFirst(['product_id = ?0 AND linked_product_id = ?1', 'bind' => [$this->_product->id, $id]]);
		if (!$link) {
			$this->flashSession->error('Produk terkait tidak ditemukan!');
		} else if ($this->request->isPost()) {
			$page = $this->dispatcher->getParam('page', 'int') ?: 1;
			if ($link->delete()) {
				$this->flashSession->success('Produk terkait berhasil dihapus!');
			}
		}
		return $this->response->redirect('/admin/product_links/index/product_id:' . $this->_product->id . ($page > 1 ? '/index/page:' . $page : ''));
	}
}
