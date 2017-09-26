<?php

namespace Application\Backend\Controllers;

use Application\Models\{Role, SalePackage, SalePackageProduct, User};
use Phalcon\Paginator\Adapter\{Model, QueryBuilder};

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
			$sale_package->assign($_POST, null, ['name', 'price']);
			$sale_package->setPublished(0);
			$sale_package->setNewPicture($_FILES['new_picture']);
			if ($sale_package->validation() && $sale_package->create()) {
				foreach ($this->request->getPost('user_product_ids') as $user_product_id) {
					$sale_package_product                  = new SalePackageProduct;
					$sale_package_product->sale_package_id = $sale_package->id;
					$sale_package_product->user_product_id = $user_product_id;
					$sale_package_product->create();
				}
				$this->flashSession->success('Penambahan paket penjualan berhasil!');
				return $this->response->redirect("/admin/users/{$this->_user->id}/sale_packages");
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
			$sale_package->assign($_POST, null, ['name', 'price']);
			$sale_package->setNewPicture($_FILES['new_picture']);
			if ($sale_package->validation() && $sale_package->update()) {
				if ($user_product_ids = $this->request->getPost('user_product_ids')) {
					SalePackageProduct::find(['sale_package_id = ?0 AND user_product_id NOT IN({user_product_ids:array})', 'bind' => [$sale_package->id, 'user_product_ids' => $user_product_ids]])->delete();
					foreach ($user_product_ids as $user_product_id) {
						if (!SalePackageProduct::findFirst(['sale_package_id = ?0 AND user_product_id = ?1', 'bind' => [$sale_package->id, $user_product_id]])) {
							$sale_package_product = new SalePackageProduct;
							$sale_package_product->create([
								'sale_package_id' => $sale_package->id,
								'user_product_id' => $user_product_id,
							]);
						}
					}
				}
				$this->flashSession->success('Update paket penjualan berhasil!');
				return $this->response->redirect("/admin/users/{$this->_user->id}/sale_packages");
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
		$products     = [];
		$search_query = $this->dispatcher->getParam('keyword', 'string') ?: null;
		$builder      = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'b.name',
				'b.stock_unit',
				'a.price',
				'a.published',
			])
			->from(['a' => 'Application\Models\UserProduct'])
			->join('Application\Models\Product', 'a.product_id = b.id', 'b')
			->orderBy('b.name, b.stock_unit')
			->where('a.user_id = ' . $this->_user->id);
		if ($search_query) {
			$keywords = preg_split('/ /', $search_query, -1, PREG_SPLIT_NO_EMPTY);
			foreach ($keywords as $keyword) {
				$builder->andWhere("b.name ILIKE '%{$keyword}%'");
			}
		}
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => null,
		]);
		$page = $paginator->getPaginate();
		foreach ($page->items as $i => $item) {
			$item->writeAttribute('rank', $i + 1);
			$products[] = $item;
		}
		if ($this->request->isPost()) {
			$user_product_ids = $this->request->getPost('user_product_ids');
		} else if ($sale_package->id) {
			$user_product_ids = [];
			foreach (SalePackageProduct::findBySalePackageId($sale_package->id) as $item) {
				$user_product_ids[] = $item->user_product_id;
			}
		}
		$this->view->page             = $paginator->getPaginate();
		$this->view->sale_package     = $sale_package;
		$this->view->products         = $products;
		$this->view->user_product_ids = $user_product_ids;
	}
}