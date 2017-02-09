<?php

namespace Application\Backend\Controllers;

use Phalcon\Db;
use Phalcon\Mvc\View;

class HomeController extends ControllerBase {
	function indexAction() {
		$daily_sales   = [];
		$monthly_sales = [];
		$annual_sales  = [];
		$best_sales    = [];
		$colors        = ['#0088cc', '#2baab1', '#734ba9'];
		foreach ($this->db->fetchAll("SELECT DATE_FORMAT(created_at, '%e') AS `date`, COUNT(1) AS amount FROM orders WHERE status = 1 AND DATE(created_at) BETWEEN ? AND ? GROUP BY `date` ORDER BY `date`", Db::FETCH_OBJ, [$this->currentDatetime->format('Y-m') . '-01', $this->currentDatetime->format('Y-m-d')]) as $sale) {
			$daily_sales[] = [$sale->date, $sale->amount];
		}
		foreach ($this->db->fetchAll("SELECT DATE_FORMAT(created_at, '%c') AS `month_number`, DATE_FORMAT(created_at, '%b') AS `month_name`, COUNT(1) AS amount FROM orders WHERE status = 1 AND DATE(created_at) BETWEEN ? AND ? GROUP BY `month_number` ORDER BY `month_number`", Db::FETCH_OBJ, [$this->currentDatetime->format('Y') . '-01-01', $this->currentDatetime->format('Y-m-d')]) as $sale) {
			$monthly_sales[] = [$sale->month_name, $sale->amount];
		}
		foreach ($this->db->fetchAll("SELECT DATE_FORMAT(created_at, '%Y') AS `year`, COUNT(1) AS amount FROM orders WHERE status = 1 GROUP BY `year` ORDER BY `year`", Db::FETCH_OBJ) as $sale) {
			$annual_sales[] = [$sale->year, $sale->amount];
		}
		foreach ($this->db->fetchAll("SELECT c.id, c.name, SUM(b.quantity) AS quantity FROM orders a JOIN order_items b ON a.id = b.order_id JOIN products c ON b.product_id = c.id WHERE a.status = 1 AND DATE_FORMAT(a.created_at, '%Y') = ? GROUP BY b.product_id ORDER BY quantity DESC LIMIT 3 OFFSET 0", Db::FETCH_OBJ, [$this->currentDatetime->format('Y')]) as $product) {
			$sales = [
				'label' => $product->name,
				'color' => array_shift($colors),
				'data'  => [],
			];
			foreach ($this->db->fetchAll("SELECT DATE_FORMAT(a.created_at, '%b') AS `month`, COUNT(1) AS amount FROM orders a JOIN order_items b ON a.id = b.order_id WHERE a.status = 1 AND b.product_id = ? AND DATE(a.created_at) BETWEEN ? AND ? GROUP BY `month` ORDER BY `month`", Db::FETCH_OBJ, [$product->id, $this->currentDatetime->format('Y') . '-01-01', $this->currentDatetime->format('Y-m-d')]) as $sale) {
				$sales['data'][] = [$sale->month, $sale->amount];
			}
			$best_sales[] = $sales;
		}
		$this->view->daily_sales   = json_encode($daily_sales, JSON_NUMERIC_CHECK);
		$this->view->monthly_sales = json_encode($monthly_sales, JSON_NUMERIC_CHECK);
		$this->view->annual_sales  = json_encode($annual_sales, JSON_NUMERIC_CHECK);
		$this->view->best_sales    = json_encode($best_sales, JSON_NUMERIC_CHECK);
		$this->view->today_orders  = $this->db->fetchColumn('SELECT COUNT(1) FROM orders WHERE DATE(created_at) = ?', [$this->currentDatetime->format('Y-m-d')]);
		$this->view->total_profit  = $this->db->fetchColumn('SELECT SUM(admin_fee) FROM orders');
		$this->view->menu          = $this->_menu();
	}

	function route404Action() {
		$this->response->setStatusCode(404, 'Not Found');
		$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
	}

	function inboxAction() {
		$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
		$this->view->start();
		$this->view->render('partials', 'inbox');
		$this->view->finish();
		$response = [
			'status' => $this->currentUser ? 1 : -1,
			'data'   => str_replace(["\n", "\t"], '', $this->view->getContent()),
		];
		$this->response->setJsonContent($response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}