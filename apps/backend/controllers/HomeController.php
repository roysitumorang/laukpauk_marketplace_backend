<?php

namespace Application\Backend\Controllers;

use Phalcon\Db;
use Phalcon\Mvc\View;

class HomeController extends ControllerBase {
	function indexAction() {
		$daily_sales   = [];
		$monthly_sales = [];
		$annual_sales  = [];
		foreach ($this->db->fetchAll("SELECT DATE_FORMAT(created_at, '%e') AS `date`, COUNT(1) AS amount FROM orders WHERE status = 1 AND DATE(created_at) BETWEEN ? AND ? GROUP BY `date` ORDER BY `date`", Db::FETCH_OBJ, [$this->currentDatetime->format('Y-m') . '-01', $this->currentDatetime->format('Y-m-d')]) as $sale) {
			$daily_sales[] = [$sale->date, $sale->amount];
		}
		foreach ($this->db->fetchAll("SELECT DATE_FORMAT(created_at, '%b') AS `month`, COUNT(1) AS amount FROM orders WHERE status = 1 AND DATE(created_at) BETWEEN ? AND ? GROUP BY `month` ORDER BY `month`", Db::FETCH_OBJ, [$this->currentDatetime->format('Y') . '-01-01', $this->currentDatetime->format('Y-m-d')]) as $sale) {
			$monthly_sales[] = [$sale->month, $sale->amount];
		}
		foreach ($this->db->fetchAll("SELECT DATE_FORMAT(created_at, '%Y') AS `year`, COUNT(1) AS amount FROM orders WHERE status = 1 GROUP BY `year` ORDER BY `year`", Db::FETCH_OBJ) as $sale) {
			$annual_sales[] = [$sale->year, $sale->amount];
		}
		$this->view->daily_sales   = json_encode($daily_sales, JSON_NUMERIC_CHECK);
		$this->view->monthly_sales = json_encode($monthly_sales, JSON_NUMERIC_CHECK);
		$this->view->annual_sales  = json_encode($annual_sales, JSON_NUMERIC_CHECK);
		$this->view->today_orders  = $this->db->fetchColumn('SELECT COUNT(1) FROM orders WHERE DATE(created_at) = ?', [$this->currentDatetime->format('Y-m-d')]);
		$this->view->total_profit  = $this->db->fetchColumn('SELECT SUM(admin_fee) FROM orders');
		$this->view->menu          = $this->_menu();
	}

	function route404Action() {
		$this->response->setStatusCode(404, 'Not Found');
		$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
	}

	function inboxAction() {
		$this->view->pick('partials/inbox');
		$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
	}
}