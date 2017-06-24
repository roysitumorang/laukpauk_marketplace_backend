<?php

namespace Application\Backend\Controllers;

use Application\Models\Product;
use Application\Models\ProductLink;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\Model;

class ProductLinksController extends ControllerBase {
	private $_product, $_limit, $current_page, $_offset;

	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		if (!($product_id = $this->dispatcher->getParam('product_id', 'int')) || !($this->_product = Product::findFirstById($product_id))) {
			$this->flashSession->error('Produk tidak ditemukan!');
			$this->response->redirect('/admin/products');
			$this->response->sendHeaders();
		}
		$this->_limit           = $this->config->per_page;
		$this->_current_page    = $this->dispatcher->getParam('page', 'int') ?: 1;
		$this->_offset          = ($this->_current_page - 1) * $this->_limit;
		$this->view->menu       = $this->_menu('Products');
		$this->view->active_tab = 'linked_products';
		$this->view->product    = $this->_product;
	}

	function indexAction() {
		$categories      = [];
		$linked_products = [];
		$paginator       = new Model([
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
		$result = $this->db->query(<<<QUERY
			SELECT
				a.id,
				a.name,
				a.stock_unit,
				b.id AS category_id,
				b.name AS category_name
			FROM
				products a
				JOIN product_categories b ON a.product_category_id = b.id
			WHERE
				a.id != {$this->_product->id} AND
				a.published = 1 AND
				b.published = 1 AND
				NOT EXISTS(SELECT 1 FROM product_links c WHERE c.product_id = {$this->_product->id} AND c.linked_product_id = a.id)
			ORDER BY b.name, a.name
QUERY
		);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($product = $result->fetch()) {
			if (!isset($categories[$product->category_id])) {
				$categories[$product->category_id] = [
					'name'     => $product->category_name,
					'products' => [],
				];
			}
			$categories[$product->category_id]['products'][] = (object) [
				'id'   => $product->id,
				'name' => $product->name . ' (' . $product->stock_unit . ')',
			];
		}
		$this->view->categories       = $categories;
		$this->view->default_category = array_keys($categories)[0];
		$this->view->linked_products  = $linked_products;
		$this->view->page             = $page;
		$this->view->pages            = $pages;
	}

	function createAction() {
		if ($this->request->isPost() &&
			($product_id = $this->request->getPost('product_id', 'int')) &&
			$product_id != $this->_product->id &&
			($linked_product = Product::findFirst(['id = ?0 AND published = 1', 'bind' => [$product_id]])) &&
			$linked_product->category->published == 1 &&
			!ProductLink::findFirst(['product_id = ?0 AND linked_product_id = ?1', 'bind' => [$this->_product->id, $linked_product->id]])) {
			$link                 = new ProductLink;
			$link->product        = $this->_product;
			$link->linked_product = $linked_product;
			if ($link->validation() && $link->create()) {
				$this->flashSession->success('Penambahan produk terkait berhasil!');
			} else {
				foreach ($link->getMessages() as $error) {
					$this->flashSession->error($error);
				}
			}
		}
		return $this->response->redirect('/admin/products/' . $this->_product->id . '/links');
	}

	function deleteAction($id) {
		$link = ProductLink::findFirst(['product_id = ?0 AND linked_product_id = ?1', 'bind' => [$this->_product->id, $id]]);
		if (!$link) {
			$this->flashSession->error('Produk terkait tidak ditemukan!');
		} else if ($this->request->isPost()) {
			$page = $this->request->get('page', 'int') ?: 1;
			if ($link->delete()) {
				$this->flashSession->success('Produk terkait berhasil dihapus!');
			}
		}
		return $this->response->redirect('/admin/products/' . $this->_product->id . '/links' . ($page > 1 ? '/index/page:' . $page : ''));
	}
}
