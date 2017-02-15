<?php

namespace Application\Backend\Controllers;

use Application\Models\Order;
use Application\Models\Village;
use DateTimeImmutable;
use Exception;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class OrdersController extends ControllerBase {
	function indexAction() {
		$limit          = $this->config->per_page;
		$current_page   = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset         = ($current_page - 1) * $limit;
		$query_string   = [];
		$parameters     = [];
		$conditions     = [];
		$status         = Order::STATUS;
		$current_status = filter_var($this->request->getQuery('status'), FILTER_VALIDATE_INT);
		if ($date = $this->request->getQuery('from')) {
			try {
				$from                 = (new DateTimeImmutable($date))->format('Y-m-d');
				$conditions[]         = "DATE(a.created_at) >= '{$from}'";
				$query_string['from'] = $from;
			} catch (Exception $e) {
				unset($from);
			}
		}
		if ($date = $this->request->getQuery('to')) {
			try {
				$to                 = (new DateTimeImmutable($date))->format('Y-m-d');
				$conditions[]       = "DATE(a.created_at) <= '{$to}'";
				$query_string['to'] = $to;
			} catch (Exception $e) {
				unset($to);
			}
		}
		if ($code = $this->request->getQuery('code', 'int')) {
			$conditions[]         = "a.code = {$code}";
			$query_string['code'] = $code;
		}
		if (array_key_exists($current_status, $status)) {
			$conditions[]           = "a.status = {$current_status}";
			$query_string['status'] = $current_status;
		}
		if ($conditions) {
			$parameters[] = implode(' AND ', $conditions);
		}
		if ($mobile_phone = $this->request->getQuery('mobile_phone')) {
			$conditions[]                 = "b.mobile_phone = '{$mobile_phone}'";
			$query_string['mobile_phone'] = $to;
		}
		$parameters['order'] = 'a.id DESC';
		$builder = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'a.code',
				'a.name',
				'a.email',
				'a.address',
				'a.village_id',
				'buyer_phone'      => 'a.mobile_phone',
				'merchant_name'    => 'b.name',
				'merchant_company' => 'b.company',
				'merchant_phone'   => 'b.mobile_phone',
				'a.status',
				'a.final_bill',
				'a.merchant_id',
				'a.buyer_id',
				'a.admin_fee',
				'a.original_bill',
				'a.ip_address',
				'a.coupon_id',
				'a.scheduled_delivery',
				'a.actual_delivery',
				'a.note',
				'a.created_at',
			])
			->addFrom('Application\Models\Order', 'a')
			->join('Application\Models\User', 'a.merchant_id = b.id', 'b');
		if ($conditions) {
			$builder->where(implode(' AND ', $conditions));
		}
		$builder->orderBy($parameters['order']);
		$paginator = new PaginatorQueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page   = $paginator->getPaginate();
		$pages  = $this->_setPaginationRange($page);
		$orders = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$orders[] = $item;
		}
		$this->view->menu                   = $this->_menu('Order');
		$this->view->orders                 = $orders;
		$this->view->pages                  = $pages;
		$this->view->page                   = $paginator->getPaginate();
		$this->view->from                   = $from;
		$this->view->to                     = $to;
		$this->view->status                 = $status;
		$this->view->current_status         = $current_status;
		$this->view->code                   = $code;
		$this->view->mobile_phone           = $mobile_phone;
		$this->view->query_string           = http_build_query($query_string);
		$this->view->total_final_bill       = $this->db->fetchColumn('SELECT SUM(a.final_bill) FROM orders a' . ($parameters[0] ? " WHERE {$parameters[0]}" : '') . ' ORDER BY ' . $parameters['order']) ?? 0;
		$this->view->total_admin_fee        = $this->db->fetchColumn('SELECT SUM(a.admin_fee) FROM orders a' . ($parameters[0] ? " WHERE {$parameters[0]}" : '') . ' ORDER BY ' . $parameters['order']) ?? 0;
		$this->view->total_orders           = $this->db->fetchColumn('SELECT COUNT(1) FROM orders');
		$this->view->pending_orders         = $this->db->fetchOne("SELECT COUNT(1) AS total, COALESCE(SUM(final_bill), 0) AS bill FROM orders WHERE `status` = 0");
		$this->view->completed_orders       = $this->db->fetchOne("SELECT COUNT(1) AS total, COALESCE(SUM(final_bill), 0) AS bill FROM orders WHERE `status` = 1");
		$this->view->total_cancelled_orders = $this->db->fetchColumn("SELECT COUNT(1) FROM orders WHERE `status` = -1");
	}

	function showAction($id) {
		if (!$order = Order::findFirstById($id)) {
			$this->flashSession->error('Order tidak ditemukan.');
			return $this->dispatcher->forward('orders');
		}
		$order->writeAttribute('status', Order::STATUS[$order->status]);
		$this->view->order   = $order;
		$this->view->village = Village::findFirst($order->village_id);
		$this->view->menu    = $this->_menu('Order');
	}
}