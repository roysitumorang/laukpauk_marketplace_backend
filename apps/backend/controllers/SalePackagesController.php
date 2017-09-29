<?php

namespace Application\Backend\Controllers;

use Application\Models\{Role, SalePackage, User};
use Ds\Vector;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\Model;

class SalePackagesController extends ControllerBase {
	private $_user;

	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		if (!($user_id = $this->dispatcher->getParam('user_id', 'int')) ||
			!($this->_user = User::findFirst(['id = ?0 AND role_id = ?1', 'bind' => [$user_id, Role::MERCHANT]]))) {
			$this->flashSession->error('Member tidak ditemukan!');
			$this->response->redirect('/admin/users');
			$this->response->sendHeaders();
		}
		$this->view->user = $this->_user;
		$this->view->menu = $this->_menu('Members');
	}

	function indexAction() {
		$limit         = $this->config->per_page;
		$current_page  = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset        = ($current_page - 1) * $limit;
		$sale_packages = [];
		$search_query  = $this->dispatcher->getParam('keyword', 'string') ?: null;
		$conditions    = ['', 'bind' => []];
		if ($search_query) {
			$keywords = preg_split('/ /', $search_query, -1, PREG_SPLIT_NO_EMPTY);
			foreach ($keywords as $i => $keyword) {
				$conditions[0]       .= ($i ? ' AND ' : '') . 'name ILIKE ?' . $i;
				$conditions['bind'][] = "%{$keyword}%";
			}
		}
		$paginator = new Model([
			'data'  => $this->_user->getRelated('salePackages'),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page  = $paginator->getPaginate();
		$pages = $this->_setPaginationRange($page);
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$sale_packages[] = $item;
		}
		$this->view->pages         = $pages;
		$this->view->page          = $paginator->getPaginate();
		$this->view->sale_packages = $sale_packages;
		$this->view->keyword       = $search_query;
		$this->view->next          = $this->request->getServer('REQUEST_URI');
	}

	function createAction() {
		$sale_package          = new SalePackage;
		$sale_package->user_id = $this->_user->id;
		if ($this->request->isPost()) {
			$sale_package->assign($_POST, null, ['name', 'price', 'stock']);
			$sale_package->setPublished(0);
			$sale_package->setNewPicture($_FILES['new_picture']);
			if ($sale_package->validation() && $sale_package->create()) {
				$this->flashSession->success('Penambahan paket belanja berhasil!');
				return $this->response->redirect("/admin/users/{$this->_user->id}/sale_packages/{$sale_package->id}/update");
			}
			foreach ($sale_package->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare($sale_package);
	}

	function updateAction($id) {
		$sale_package = $this->_user->getRelated('salePackages', ['id = ?0', 'bind' => [$id]])->getFirst();
		if (!$sale_package) {
			$this->flashSession->error('Paket belanja tidak ditemukan!');
			return $this->response->redirect("/admin/users/{$this->_user->id}/sale_packages");
		}
		if ($this->request->isPost()) {
			$sale_package->assign($_POST, null, ['name', 'price', 'stock']);
			$sale_package->setNewPicture($_FILES['new_picture']);
			if ($sale_package->validation() && $sale_package->update()) {
				$this->flashSession->success('Update paket belanja berhasil!');
				return $this->response->redirect("/admin/users/{$this->_user->id}/sale_packages/{$sale_package->id}/update");
			}
			foreach ($sale_package->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare($sale_package);
	}

	function toggleStatusAction($id) {
		$sale_package = $this->_user->getRelated('salePackages', ['id = ?0', 'bind' => [$id]])->getFirst();
		if (!$sale_package) {
			$this->flashSession->error('Paket belanja tidak ditemukan!');
			return $this->response->redirect("/admin/users/{$this->_user->id}/sale_packages");
		}
		$sale_package->update(['published' => $sale_package->published ? 0 : 1]);
		return $this->response->redirect($this->request->get('next'));
	}

	function deletePictureAction($id) {
		$sale_package = $this->_user->getRelated('salePackages', ['id = ?0 AND picture IS NOT NULL', 'bind' => [$id]])->getFirst();
		if (!$sale_package) {
			$this->flashSession->error('Paket belanja tidak ditemukan!');
			return $this->response->redirect("/admin/users/{$this->_user->id}/sale_packages");
		}
		$sale_package->deletePicture();
		return $this->response->redirect("/admin/users/{$this->_user->id}/sale_packages/{$sale_package->id}/update");
	}

	private function _prepare(SalePackage $sale_package = null) {
		$existing_products = new Vector;
		$new_products      = new Vector;
		$new_product       = $this->request->getPost('new_product') ?: [];
		$quantities        = range(1, 10);
		if ($sale_package->id) {
			$result = $this->db->query(<<<QUERY
				SELECT
					a.id,
					c.name,
					c.stock_unit,
					b.price,
					b.published,
					a.quantity
				FROM
					sale_package_product a
					JOIN user_product b ON a.user_product_id = b.id
					JOIN products c ON b.product_id = c.id
				WHERE
					a.sale_package_id = {$sale_package->id}
				ORDER BY
					name,
					stock_unit
QUERY
			);
			$result->setFetchMode(Db::FETCH_OBJ);
			$i = 0;
			while ($item = $result->fetch()) {
				if ($this->request->isPost()) {
					$item->quantity = $this->request->getPost('product')[$item->id]['quantity'];
				}
				$item->rank = ++$i;
				$existing_products->push($item);
			}
			$result = $this->db->query(<<<QUERY
				SELECT
					a.id AS user_product_id,
					b.name,
					b.stock_unit
				FROM
					user_product a
					JOIN products b ON a.product_id = b.id
					LEFT JOIN sale_package_product c ON a.id = c.user_product_id AND c.sale_package_id = {$sale_package->id}
				WHERE
					a.user_id = {$this->_user->id} AND
					c.id IS NULL
				ORDER BY
					name,
					stock_unit
QUERY
			);
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($item = $result->fetch()) {
				$new_products->push($item);
			}
		}
		$this->view->sale_package      = $sale_package;
		$this->view->existing_products = $existing_products;
		$this->view->new_products      = $new_products;
		$this->view->new_product       = $new_product;
		$this->view->quantities        = $quantities;
	}
}