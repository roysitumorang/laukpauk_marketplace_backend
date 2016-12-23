<?php

namespace Application\Backend\Controllers;

use Application\Models\Order;
use Application\Models\Village;
use DateTimeImmutable;
use Exception;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class OrdersController extends ControllerBase {
	function indexAction() {
		$limit          = $this->config->per_page;
		$current_page   = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset         = ($current_page - 1) * $limit;
		$parameters     = [];
		$conditions     = [];
		$status         = Order::STATUS;
		$current_status = $this->request->getQuery('status', 'int');
		if ($current_status && array_key_exists($current_status, $status)) {
			$conditions[] = "status = {$current_status}";
		}
		if ($code = $this->request->getQuery('code', 'int')) {
			$conditions[] = "code = {$code}";
		}
		if ($date = $this->request->getQuery('from')) {
			try {
				$from = (new DateTimeImmutable($date))->format('Y-m-d');
			} catch (Exception $e) {
				unset($from);
			}
			if ($from) {
				$conditions[] = "DATE(created_at) >= '{$from}'";
			}
		}
		if ($date = $this->request->getQuery('to')) {
			try {
				$to = (new DateTimeImmutable($date))->format('Y-m-d');
			} catch (Exception $e) {
				unset($to);
			}
			if ($to) {
				$conditions[] = "DATE(created_at) <= '{$to}'";
			}
		}
		if ($conditions) {
			$parameters[] = implode(' AND ', $conditions);
		}
		$parameters['order'] = 'id DESC';
		$paginator = new PaginatorModel([
			'data'  => Order::find($parameters),
			'limit' => $limit,
			'page'  => $current_page,
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
		$this->view->total_final_bill       = $this->db->fetchColumn('SELECT SUM(final_bill) FROM orders' . ($parameters[0] ? " WHERE {$parameters[0]}" : '') . $parameter['order']) ?? 0;
		$this->view->total_admin_fee        = $this->db->fetchColumn('SELECT SUM(admin_fee) FROM orders' . ($parameters[0] ? " WHERE {$parameters[0]}" : '') . $parameter['order']) ?? 0;
		$this->view->total_orders           = $this->db->fetchColumn('SELECT COUNT(1) FROM orders');
		$this->view->pending_orders         = $this->db->fetchOne("SELECT COUNT(1) AS total, COALESCE(SUM(final_bill), 0) AS bill FROM orders WHERE `status` = 0");
		$this->view->completed_orders       = $this->db->fetchOne("SELECT COUNT(1) AS total, COALESCE(SUM(final_bill), 0) AS bill FROM orders WHERE `status` = 1");
		$this->view->total_cancelled_orders = $this->db->fetchColumn("SELECT COUNT(1) FROM orders WHERE `status` = -1");
	}

	function showAction($id) {
		if (!$order = Order::findFirst($id)) {
			$this->flashSession->error('Order tidak ditemukan.');
			return $this->dispatcher->forward('orders');
		}
		$this->view->order   = $order;
		$this->view->village = Village::findFirst($order->village_id);
		$this->view->menu    = $this->_menu('Order');
	}
}