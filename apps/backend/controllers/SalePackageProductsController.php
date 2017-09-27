<?php

namespace Application\Backend\Controllers;

use Application\Models\{Role, SalePackage, SalePackageProduct, User};

class SalePackageProductsController extends ControllerBase {
	private $_user, $_sale_package;

	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		if (!($user_id = $this->dispatcher->getParam('user_id', 'int')) ||
			!($this->_user = User::findFirst(['id = ?0 AND role_id = ?1', 'bind' => [$user_id, Role::MERCHANT]]))) {
			$this->flashSession->error('Member tidak ditemukan!');
			$this->response->redirect('/admin/users');
			$this->response->sendHeaders();
		}
		if (!($sale_package_id = $this->dispatcher->getParam('sale_package_id', 'int')) ||
			!($this->_sale_package = SalePackage::findFirst(['id = ?0 AND user_id = ?1', 'bind' => [$sale_package_id, $this->_user->id]]))) {
			$this->flashSession->error('Paket belanja tidak ditemukan!');
			$this->response->redirect("/admin/users/{$this->_user->id}/sale_packages");
			$this->response->sendHeaders();
		}
	}

	function createAction() {
		$sale_package_product = new SalePackageProduct(['sale_package_id' => $this->_sale_package->id]);
		$sale_package_product->assign($this->request->getPost('new_product'), null, ['user_product_id', 'quantity']);
		if ($sale_package_product->validation() && $sale_package_product->create()) {
			$this->flashSession->success('Penambahan paket belanja berhasil!');
			return $this->response->redirect("/admin/users/{$this->_user->id}/sale_packages/{$this->_sale_package->id}/update");
		}
		foreach ($sale_package_product->getMessages() as $error) {
			$this->flashSession->error($error);
		}
		$this->dispatcher->forward(['controller' => 'SalePackages', 'action' => 'update']);
	}

	function updateAction() {
		foreach (SalePackageProduct::findBySalePackageId($this->_sale_package->id) as $product) {
			$product->update(['quantity' => $this->request->getPost('product')[$product->id]['quantity']]);
		}
		$this->flashSession->success('Update paket belanja berhasil!');
		return $this->response->redirect("/admin/users/{$this->_user->id}/sale_packages/{$this->_sale_package->id}/update");
	}

	function deleteAction($id) {
		$sale_package_product = SalePackageProduct::findFirst(['id = ?0 AND sale_package_id = ?1', 'bind' => [$id, $this->_sale_package->id]]);
		if (!$sale_package_product) {
			$this->flashSession->error('Paket belanja tidak ditemukan!');
		} else {
			$sale_package_product->delete();
			$this->flashSession->success('Paket belanja berhasil dihapus!');
		}
		return $this->response->redirect("/admin/users/{$this->_user->id}/sale_packages/{$this->_sale_package->id}/update");
	}
}