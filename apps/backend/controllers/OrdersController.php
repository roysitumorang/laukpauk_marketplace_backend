<?php

namespace Application\Backend\Controllers;

use Application\Models\Order;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class OrdersController extends BaseController {
	function indexAction() {
		$limit          = $this->config->per_page;
		$current_page   = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset         = ($current_page - 1) * $limit;
		$status         = Order::STATUS;
		$current_status = $this->request->getQuery('status', 'int');
		if ($current_status && !array_key_exists($current_status, $status)) {
			$current_status = null;
		}
		$paginator = new PaginatorModel([
			'data'  => Order::find(),
			'limit' => $limit,
			'page'  => $current_page,
		]);
		$page   = $paginator->getPaginate();
		$pages  = $this->_setPaginationRange($page);
		$orders = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('status', $status[$item->status]);
			$orders[] = $item;
		}
		$this->view->menu                   = $this->_menu('Order');
		$this->view->users                  = $users;
		$this->view->pages                  = $pages;
		$this->view->page                   = $paginator->getPaginate();
		$this->view->status                 = $status;
		$this->view->current_status         = $current_status;
		$this->view->total_orders           = $this->db->fetchColumn('SELECT COUNT(1) FROM orders');
		$this->view->pending_orders         = $this->db->fetchOne("SELECT COUNT(1) AS total, COALESCE(SUM(final_bill), 0) AS bill FROM orders WHERE `status` = 0");
		$this->view->completed_orders       = $this->db->fetchOne("SELECT COUNT(1) AS total, COALESCE(SUM(final_bill), 0) AS bill FROM orders WHERE `status` = 1");
		$this->view->total_cancelled_orders = $this->db->fetchColumn("SELECT COUNT(1) FROM orders WHERE `status` = -1");
	}
}