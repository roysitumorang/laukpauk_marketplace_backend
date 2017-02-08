<?php

namespace Application\Backend\Controllers;

use Application\Models\City;
use Application\Models\Coupon;
use Application\Models\CouponVillage;
use Phalcon\Paginator\Adapter\QueryBuilder;

class CouponVillagesController extends ControllerBase {
	private $_city, $_coupon;

	function initialize() {
		parent::initialize();
		if (!($coupon_id = $this->dispatcher->getParam('coupon_id')) || !($this->_coupon = Coupon::findFirstById($coupon_id))) {
			$this->flashSession->error('Kupon tidak ditemukan.');
			$this->response->redirect('coupons');
			return false;
		}
		$this->_city = City::findFirstByName('Medan');
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$builder      = $this->modelsManager->createBuilder()
			->columns(['b.id', 'b.name', 'subdistrict' => 'c.name'])
			->from(['a' => 'Application\Models\CouponVillage'])
			->join('Application\Models\Village', 'a.village_id = b.id', 'b')
			->join('Application\Models\Subdistrict', 'b.subdistrict_id = c.id', 'c')
			->where('a.coupon_id = ' . $this->_coupon->id)
			->orderBy('c.name, b.name');
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page            = $paginator->getPaginate();
		$pages           = $this->_setPaginationRange($page);
		$coupon_villages = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$coupon_villages[] = $item;
		}
		$subdistricts = $this->_city->getRelated('subdistricts', ['order' => 'name']);
		$villages     = [];
		foreach ($subdistricts as $subdistrict) {
			$villages[$subdistrict->id] = [];
			foreach ($subdistrict->getRelated('villages', ['order' => 'name']) as $village) {
				$villages[$subdistrict->id][] = (object) [
					'id'   => $village->id,
					'name' => $village->name,
				];
			}
		}
		$this->view->menu            = $this->_menu('Products');
		$this->view->coupon          = $this->_coupon;
		$this->view->coupon_villages = $coupon_villages;
		$this->view->page            = $page;
		$this->view->pages           = $pages;
		$this->view->subdistricts    = $subdistricts;
		$this->view->villages        = $villages;
	}

	function createAction() {
		if ($this->request->isPost() && ($village_id = $this->request->getPost('village_id', 'int')) && $this->db->fetchColumn("SELECT COUNT(1) FROM villages a JOIN subdistricts b ON a.subdistrict_id = b.id JOIN cities c ON b.city_id = c.id WHERE c.id = {$this->_city->id} AND NOT EXISTS(SELECT 1 FROM coupon_villages d WHERE d.coupon_id = {$this->_coupon->id} AND d.village_id = a.id) AND a.id = {$village_id}")) {
			$coupon_village             = new CouponVillage;
			$coupon_village->coupon_id  = $this->_coupon->id;
			$coupon_village->village_id = $village_id;
			if ($coupon_village->create()) {
				$this->flashSession->success('Penambahan kelurahan berhasil.');
			}
		}
		$this->response->redirect('/admin/coupon_villages/index/coupon_id:' . $this->_coupon->id);
	}

	function deleteAction($id) {
		$coupon_village = CouponVillage::findFirst([
			'coupon_id = ?0 AND village_id = ?1',
			'bind' => [$this->_coupon->id, $id]
		]);
		if (!$coupon_village) {
			$this->flashSession->error('Kelurahan tidak ditemukan.');
		} else {
			$coupon_village->delete();
			$this->flashSession->success('Kelurahan berhasil dihapus.');
		}
		return $this->response->redirect('/admin/coupon_villages/index/coupon_id:' . $this->_coupon->id);
	}
}
