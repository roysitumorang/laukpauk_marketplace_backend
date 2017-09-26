<?php

namespace Application\Backend\Controllers;

use Application\Models\{Role, SalePackage, SalePackageProduct, User};
use Ds\Vector;
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
			$sale_package->assign($_POST, null, ['name', 'price', 'stock']);
			$sale_package->setPublished(0);
			$sale_package->setNewPicture($_FILES['new_picture']);
			if ($sale_package->validation() && $sale_package->create()) {
				$products = array_filter($this->request->getPost('products') ?: [], function($v, $k) {
					return $k == $v['user_product_id'];
				}, ARRAY_FILTER_USE_BOTH);
				foreach ($products as $item) {
					$product = filter_var_array($item, [
						'user_product_id' => FILTER_VALIDATE_INT,
						'quantity'        => FILTER_VALIDATE_INT,
					]);
					if (!$product['user_product_id']) {
						continue;
					}
					$sale_package_product = new SalePackageProduct;
					$sale_package_product->create([
						'sale_package_id' => $sale_package->id,
						'user_product_id' => $product['user_product_id'],
						'quantity'        => $product['quantity'] ?: 0,
					]);
				}
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
				$products = array_filter($this->request->getPost('products') ?: [], function($v, $k) {
					return $k == $v['user_product_id'];
				}, ARRAY_FILTER_USE_BOTH);
				$params = ['sale_package_id = ?0', 'bind' => [$sale_package->id]];
				if ($products) {
					$params[0]                         .= ' AND user_product_id NOT IN({user_product_ids:array})';
					$params['bind']['user_product_ids'] = array_keys($products);
				}
				SalePackageProduct::find($params)->delete();
				foreach ($products as $item) {
					$product = filter_var_array($item, [
						'user_product_id' => FILTER_VALIDATE_INT,
						'quantity'        => FILTER_VALIDATE_INT,
						'id'              => FILTER_VALIDATE_INT,
					]);
					if (!$product['user_product_id']) {
						continue;
					}
					if ($product['id'] && $sale_package_product = SalePackageProduct::findFirst(['id = ?0 AND sale_package_id = ?1', 'bind' => [$product['id'], $sale_package->id]])) {
						$sale_package_product->update(['quantity' => $product['quantity'] ?: 0]);
						continue;
					}
					$sale_package_product = new SalePackageProduct;
					$sale_package_product->create([
						'sale_package_id' => $sale_package->id,
						'user_product_id' => $product['user_product_id'],
						'quantity'        => $product['quantity'] ?: 0,
					]);
				}
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
		$posted_products  = [];
		$products         = [];
		$user_product_ids = new Vector;
		$search_query     = $this->dispatcher->getParam('keyword', 'string') ?: null;
		if ($this->request->isPost()) {
			$posted_products = array_filter($this->request->getPost('products') ?: [], function($v, $k) {
				return $k == $v['user_product_id'];
			}, ARRAY_FILTER_USE_BOTH);
			$user_product_ids = new Vector(array_keys($posted_products));
		} else if ($sale_package->id) {
			foreach (SalePackageProduct::findBySalePackageId($sale_package->id) as $item) {
				$user_product_ids->push($item->user_product_id);
			}
		}
		$builder = $this->modelsManager->createBuilder()
			->columns([
				'c.id',
				'user_product_id' => 'a.id',
				'b.name',
				'b.stock_unit',
				'a.price',
				'a.published',
				'c.quantity'
			])
			->from(['a' => 'Application\Models\UserProduct'])
			->join('Application\Models\Product', 'a.product_id = b.id', 'b')
			->leftJoin('Application\Models\SalePackageProduct', 'a.id = c.user_product_id AND c.sale_package_id ' . ($sale_package->id ? "= {$sale_package->id}" : 'IS NULL'), 'c')
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
			if ($this->request->isPost() && $user_product_ids->contains($item->user_product_id)) {
				$item->quantity = $posted_products[$item->user_product_id]['quantity'];
			}
			$products[] = $item;
		}
		$this->view->page             = $paginator->getPaginate();
		$this->view->sale_package     = $sale_package;
		$this->view->products         = $products;
		$this->view->user_product_ids = $user_product_ids;
	}
}