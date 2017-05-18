<?php

namespace Application\Backend\Controllers;

use DateInterval;
use DatePeriod;
use Ds\Vector;
use Phalcon\Db;
use Phalcon\Mvc\View;

class HomeController extends ControllerBase {
	function indexAction() {
		$daily_sales   = [];
		$monthly_sales = [];
		$annual_sales  = [];
		$best_sales    = [];
		$colors        = ['#0088cc', '#2baab1', '#734ba9'];
		$dates         = new Vector;
		foreach (new DatePeriod($this->currentDatetime->setDate($this->currentDatetime->format('Y'), $this->currentDatetime->format('n'), 1), new DateInterval('P1D'), $this->currentDatetime->modify('+1 day')) as $date) {
			$dates->push("('" . $date->format('Y-m-d') . "')");
		}
		$this->db->execute('INSERT INTO dates ("name") VALUES ' . $dates->join(',') . ' ON CONFLICT ("name") DO NOTHING');
		foreach ($this->db->fetchAll("SELECT TO_CHAR(a.name, 'DD') AS date, COUNT(b.id) AS amount FROM dates a LEFT JOIN orders b ON a.name = DATE(b.created_at) AND b.status = 1 WHERE a.name BETWEEN ? AND ? GROUP BY date ORDER BY date", Db::FETCH_OBJ, [$this->currentDatetime->format('Y-m') . '-01', $this->currentDatetime->format('Y-m-d')]) as $sale) {
			$daily_sales[] = [$sale->date, $sale->amount];
		}
		foreach ($this->db->fetchAll("SELECT TO_CHAR(created_at, 'MM') AS month_number, TO_CHAR(created_at, 'Mon') AS month_name, COUNT(1) AS amount FROM orders WHERE status = 1 AND DATE(created_at) BETWEEN ? AND ? GROUP BY month_number, month_name ORDER BY month_number", Db::FETCH_OBJ, [$this->currentDatetime->format('Y') . '-01-01', $this->currentDatetime->format('Y-m-d')]) as $sale) {
			$monthly_sales[] = [$sale->month_name, $sale->amount];
		}
		foreach ($this->db->fetchAll("SELECT TO_CHAR(created_at, 'YYYY') AS year, COUNT(1) AS amount FROM orders WHERE status = 1 GROUP BY year ORDER BY year", Db::FETCH_OBJ) as $sale) {
			$annual_sales[] = [$sale->year, $sale->amount];
		}
		foreach ($this->db->fetchAll("SELECT c.name, SUM(b.quantity) AS quantity FROM orders a JOIN order_items b ON a.id = b.order_id JOIN products c ON b.product_id = c.id WHERE a.status = 1 AND TO_CHAR(a.created_at, 'YYYY') = ? GROUP BY c.id ORDER BY quantity DESC LIMIT 3 OFFSET 0", Db::FETCH_OBJ, [$this->currentDatetime->format('Y')]) as $product) {
			$sales = [
				'label' => $product->name,
				'color' => array_shift($colors),
				'data'  => [],
			];
			foreach ($this->db->fetchAll("SELECT TO_CHAR(a.created_at, 'Mon') AS month, COUNT(1) AS amount FROM orders a JOIN order_items b ON a.id = b.order_id WHERE a.status = 1 AND b.product_id = ? AND DATE(a.created_at) BETWEEN ? AND ? GROUP BY month ORDER BY month", Db::FETCH_OBJ, [$product->id, $this->currentDatetime->format('Y') . '-01-01', $this->currentDatetime->format('Y-m-d')]) as $sale) {
				$sales['data'][] = [$sale->month, $sale->amount];
			}
			$best_sales[] = $sales;
		}
		$this->view->daily_sales   = json_encode($daily_sales, JSON_NUMERIC_CHECK);
		$this->view->monthly_sales = json_encode($monthly_sales, JSON_NUMERIC_CHECK);
		$this->view->annual_sales  = json_encode($annual_sales, JSON_NUMERIC_CHECK);
		$this->view->best_sales    = json_encode($best_sales, JSON_NUMERIC_CHECK);
		$this->view->today_orders  = $this->db->fetchColumn('SELECT COUNT(1) FROM orders WHERE DATE(created_at) = ?', [$this->currentDatetime->format('Y-m-d')]);
		$this->view->total_profit  = $this->db->fetchColumn('SELECT SUM(admin_fee) FROM orders WHERE status = 1');
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
			'data'   => strtr($this->view->getContent(), ["\n" => '', "\t" => '']),
		];
		$this->response->setJsonContent($response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}