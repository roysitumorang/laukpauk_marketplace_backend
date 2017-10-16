<?php

namespace Application\Backend\Controllers;

use Application\Models\{Coupon, Order, Release};
use DateTime;
use IntlDateFormatter;
use Phalcon\Paginator\Adapter\QueryBuilder;

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
			'd MMMM yyyy'
		);
	}

	function indexAction() {
		$coupons        = [];
		$limit          = $this->config->per_page;
		$current_page   = $this->dispatcher->getParam('page', 'int', 1);
		$offset         = ($current_page - 1) * $limit;
		$keyword        = $this->dispatcher->getParam('keyword', 'string');
		$current_status = $this->dispatcher->getParam('status', 'int');
		$builder        = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'a.code',
				'a.effective_date',
				'a.expiry_date',
				'a.price_discount',
				'a.discount_type',
				'a.status',
				'a.multiple_use',
				'a.minimum_purchase',
				'a.release_id',
				'a.maximum_usage',
				'minimum_version' => "STRING_AGG(DISTINCT b.version, '')",
				'total_usage'     => 'SUM(c.discount) / a.price_discount',
			])
			->from(['a' => Coupon::class])
			->leftJoin(Release::class, 'a.release_id = b.id', 'b')
			->leftJoin(Order::class, 'a.id = c.coupon_id AND c.status = 1', 'c')
			->groupBy('a.id')
			->orderBy('a.id DESC');
		if ($keyword) {
			$builder->andWhere('a.code ILIKE :code:', ['code' => "%{$keyword}%"]);
		}
		if (ctype_digit($current_status)) {
			$builder->andWhere("a.status = :status:", ['status' => $current_status]);
		}
		$pagination = (new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]))->getPaginate();
		foreach ($pagination->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('multiple_use', Coupon::USAGE_TYPES[$item->multiple_use]);
			$item->writeAttribute('effective_date_start', $this->_date_formatter->format(new DateTime($item->effective_date, $this->currentDatetime->getTimezone())));
			$item->writeAttribute('effective_date_end', $this->_date_formatter->format((new DateTime($item->expiry_date, $this->currentDatetime->getTimezone()))->modify('-1 day')));
			$coupons[] = $item;
		}
		$this->view->setVars([
			'coupons'        => $coupons,
			'pagination'     => $pagination,
			'pages'          => $this->_setPaginationRange($pagination),
			'coupon_status'  => Coupon::STATUS,
			'keyword'        => $keyword,
			'current_status' => $current_status,
			'current_date'   => $this->currentDatetime->format('Y-m-d'),
		]);
	}

	function showAction($id) {
		if (!$coupon = Coupon::findFirst($id)) {
			$this->flashSession->error('Kupon tidak ditemukan.');
			return $this->response->redirect('/admin/coupons');
		}
		$coupon->effective_date_start = $this->_date_formatter->format(new DateTime($coupon->effective_date, $this->currentDatetime->getTimezone()));
		$coupon->effective_date_end   = $this->_date_formatter->format((new DateTime($coupon->expiry_date, $this->currentDatetime->getTimezone()))->modify('-1 day'));
		$coupon->usage_type           = Coupon::USAGE_TYPES[$coupon->multiple_use];
		$this->view->coupon           = $coupon;
	}

	function createAction() {
		$coupon = new Coupon;
		if ($this->request->isPost()) {
			$this->_assignModelAttributes($coupon);
			if ($coupon->validation() && $coupon->create()) {
				$this->flashSession->success('Penambahan kupon berhasil.');
				return $this->response->redirect('/admin/coupons');
			}
			$this->flashSession->error('Penambahan kupon tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($coupon->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepareForm($coupon);
	}

	function updateAction($id) {
		if (!$coupon = Coupon::findFirst($id)) {
			$this->flashSession->error('Kupon tidak ditemukan.');
			return $this->dispatcher->forward('coupons');
		}
		if ($this->request->isPost()) {
			$this->_assignModelAttributes($coupon);
			if ($coupon->validation() && $coupon->update()) {
				$this->flashSession->success('Update kupon berhasil.');
				return $this->response->redirect('/admin/coupons');
			}
			$this->flashSession->error('Penambahan kupon tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($coupon->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepareForm($coupon);
	}

	function toggleStatusAction($id) {
		$keyword = $this->dispatcher->getParam('keyword');
		$status  = $this->dispatcher->getParam('status', 'int');
		$page    = $this->dispatcher->getParam('page', 'int', 1);
		if (!$this->request->isPost() || !($coupon = Coupon::findFirst(['id = ?0 AND expiry_date > ?1', 'bind' => [$id, $this->currentDatetime->format('Y-m-d')]]))) {
			$this->flashSession->error('Kupon tidak ditemukan.');
		} else {
			$coupon->update(['status' => $coupon->status ? 0 : 1]);
		}
		return $this->response->redirect("/admin/coupons/index" . ($keyword ? "/keyword:{$keyword}" : '') . ($status ? "/status:{$status}" : '') . ($page > 1 ? "/page:{$page}" : ''));
	}

	private function _prepareForm(Coupon &$coupon) {
		$this->view->setVars([
			'coupon'         => $coupon,
			'discount_types' => Coupon::DISCOUNT_TYPES,
			'coupon_status'  => Coupon::STATUS,
			'usage_types'    => Coupon::USAGE_TYPES,
			'releases'       => Release::find(["user_type = 'buyer'", 'columns' => 'id, version', 'order' => 'version DESC']),
		]);
	}

	private function _assignModelAttributes(Coupon &$coupon) {
		if (!$coupon->id) {
			$coupon->code = $this->request->getPost('code');
		}
		$coupon->assign($_POST, null, [
			'price_discount',
			'discount_type',
			'effective_date',
			'expiry_date',
			'minimum_purchase',
			'status',
			'multiple_use',
			'release_id',
			'maximum_usage',
			'description',
		]);
	}
}
