<?php

namespace Application\Frontend\Controllers;

use Application\Models\Coupon;
use DateTime;
use IntlDateFormatter;
use Phalcon\Paginator\Adapter\Model;

class CouponsController extends ControllerBase {
	private $_date_formatter;

	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		$this->view->menu      = $this->_menu('Products');
		$this->_date_formatter = new IntlDateFormatter(
			'id_ID',
			IntlDateFormatter::FULL,
			IntlDateFormatter::NONE,
			$this->currentDatetime->getTimezone(),
			IntlDateFormatter::GREGORIAN,
			'EEEE, d MMMM yyyy'
		);
	}

	function indexAction() {
		$limit          = $this->config->per_page;
		$current_page   = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset         = ($current_page - 1) * $limit;
		$keyword        = $this->request->get('keyword', 'string');
		$current_status = filter_var($this->request->get('status'), FILTER_VALIDATE_INT);
		$conditions     = ['user_id = :user_id:', 'bind' => ['user_id' => $this->currentUser->id], 'order' => 'id DESC'];
		if ($keyword) {
			$conditions[0]             .= ' AND code ILIKE :code:';
			$conditions['bind']['code'] = "%{$keyword}%";
		}
		if ($current_status) {
			$conditions[0]               .= ' AND status = :status:';
			$conditions['bind']['status'] = $current_status;
		}
		$paginator = new Model([
			'data'  => Coupon::find($conditions),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page    = $paginator->getPaginate();
		$pages   = $this->_setPaginationRange($page);
		$coupons = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('multiple_use', Coupon::USAGE_TYPES[$item->multiple_use]);
			$item->writeAttribute('effective_date_start', $this->_date_formatter->format(new DateTime($item->effective_date, $this->currentDatetime->getTimezone())));
			$item->writeAttribute('effective_date_end', $this->_date_formatter->format((new DateTime($item->expiry_date, $this->currentDatetime->getTimezone()))->modify('-1 day')));
			$coupons[] = $item;
		}
		$this->view->coupons        = $coupons;
		$this->view->page           = $page;
		$this->view->pages          = $pages;
		$this->view->status         = Coupon::STATUS;
		$this->view->keyword        = $keyword;
		$this->view->current_status = $current_status;
	}

	function showAction($id) {
		$coupon = Coupon::findFirst(['user_id = ?0 AND id = ?1', 'bind' => [$this->currentUser->id, $id]]);
		if (!$coupon) {
			$this->flashSession->error('Kupon tidak ditemukan.');
			return $this->response->redirect('/coupons');
		}
		$coupon->effective_date_start = $this->_date_formatter->format(new DateTime($coupon->effective_date, $this->currentDatetime->getTimezone()));
		$coupon->effective_date_end   = $this->_date_formatter->format((new DateTime($coupon->expiry_date, $this->currentDatetime->getTimezone()))->modify('-1 day'));
		$coupon->usage_type           = Coupon::USAGE_TYPES[$coupon->multiple_use];
		$this->view->coupon           = $coupon;
	}

	function createAction() {
		$coupon = new Coupon;
		if ($this->request->isPost()) {
			$this->_set_model_attributes($coupon);
			if ($coupon->validation() && $coupon->create()) {
				$this->flashSession->success('Penambahan kupon berhasil.');
				return $this->response->redirect('/coupons');
			}
			$this->flashSession->error('Penambahan kupon tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($coupon->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_form($coupon);
	}

	function updateAction($id) {
		$coupon = Coupon::findFirst(['user_id = ?0 AND id = ?1', 'bind' => [$this->currentUser->id, $id]]);
		if (!$coupon) {
			$this->flashSession->error('Kupon tidak ditemukan.');
			return $this->dispatcher->forward('coupons');
		}
		if ($this->request->isPost()) {
			$this->_set_model_attributes($coupon);
			if ($coupon->validation() && $coupon->update()) {
				$this->flashSession->success('Update kupon berhasil.');
				return $this->response->redirect('/coupons');
			}
			$this->flashSession->error('Penambahan kupon tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($coupon->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_form($coupon);
	}

	private function _prepare_form(Coupon &$coupon) {
		$this->view->coupon         = $coupon;
		$this->view->discount_types = Coupon::DISCOUNT_TYPES;
		$this->view->status         = Coupon::STATUS;
		$this->view->usage_types    = Coupon::USAGE_TYPES;
	}

	private function _set_model_attributes(Coupon &$coupon) {
		if (!$coupon->id) {
			$coupon->user_id = $this->currentUser->id;
			$coupon->code    = $this->request->getPost('code');
		}
		$coupon->assign([
			'price_discount'   => $this->request->getPost('price_discount'),
			'discount_type'    => $this->request->getPost('discount_type'),
			'effective_date'   => $this->request->getPost('effective_date'),
			'expiry_date'      => $this->request->getPost('expiry_date'),
			'minimum_purchase' => $this->request->getPost('minimum_purchase'),
			'status'           => $this->request->getPost('status'),
			'multiple_use'     => $this->request->getPost('multiple_use'),
			'description'      => $this->request->getPost('description'),
		]);
	}
}
