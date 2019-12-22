<?php

namespace Application\Backend\Controllers;

use DateInterval;
use DatePeriod;
use Ds\Vector;
use Phalcon\Db\Enum;
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
		foreach ($this->db->fetchAll("SELECT TO_CHAR(a.name, 'DD') AS date, COUNT(b.id) AS amount FROM dates a LEFT JOIN orders b ON a.name = DATE(b.created_at) AND b.status = 1 WHERE a.name BETWEEN ? AND ? GROUP BY date ORDER BY date", Enum::FETCH_OBJ, [$this->currentDatetime->format('Y-m') . '-01', $this->currentDatetime->format('Y-m-d')]) as $sale) {
			$daily_sales[] = [$sale->date, $sale->amount];
		}

		foreach ($this->db->fetchAll("SELECT a.id, a.name AS month, COUNT(b.id) AS amount FROM months a LEFT JOIN orders b ON a.name = TO_CHAR(b.created_at, 'Mon') AND b.status = 1 AND b.created_at BETWEEN ? AND ? WHERE a.id <= ? GROUP BY a.id ORDER BY a.id", Enum::FETCH_OBJ, [$this->currentDatetime->format('Y') . '-01-01 00:00:00', $this->currentDatetime->format('Y-m-d H:i:s.u'), $this->currentDatetime->format('n')]) as $sale) {
			$monthly_sales[] = [$sale->month, $sale->amount];
		}
		foreach ($this->db->fetchAll("SELECT TO_CHAR(created_at, 'YYYY') AS year, COUNT(1) AS amount FROM orders WHERE status = 1 GROUP BY year ORDER BY year", Enum::FETCH_OBJ) as $sale) {
			$annual_sales[] = [$sale->year, $sale->amount];
		}
		foreach ($this->db->fetchAll("SELECT c.id, c.name, c.stock_unit, SUM(b.quantity) AS quantity FROM orders a JOIN order_product b ON a.id = b.order_id JOIN products c ON b.product_id = c.id WHERE a.status = 1 AND a.created_at BETWEEN ? AND ? GROUP BY c.id ORDER BY quantity DESC LIMIT 10 OFFSET 0", Enum::FETCH_OBJ, [$this->currentDatetime->format('Y') . '-01-01 00:00:00', $this->currentDatetime->format('Y-m-d H:i:s.u')]) as $product) {
			$sales = [
				'label' => $product->name . ' (' . $product->stock_unit . ')',
				'color' => array_shift($colors),
				'data'  => [],
			];
			foreach ($this->db->fetchAll("SELECT a.id, SUM(COALESCE(c.quantity, 0)) AS amount FROM months a LEFT JOIN orders b ON a.name = TO_CHAR(b.created_at, 'Mon') AND b.status = 1 AND b.created_at BETWEEN ? AND ? LEFT JOIN order_product c ON b.id = c.order_id AND c.product_id = ? WHERE a.id <= ? GROUP BY a.id ORDER BY a.id", Enum::FETCH_OBJ, [$this->currentDatetime->format('Y') . '-01-01 00:00:00', $this->currentDatetime->format('Y-m-d H:i:s.u'), $product->id, $this->currentDatetime->format('n')]) as $sale) {
				$sales['data'][] = [$sale->id, $sale->amount];
			}
			$best_sales[] = $sales;
		}
		$this->view->daily_sales   = json_encode($daily_sales, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		$this->view->monthly_sales = json_encode($monthly_sales, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		$this->view->annual_sales  = json_encode($annual_sales, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		$this->view->best_sales    = json_encode($best_sales, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		$this->view->today_orders  = $this->db->fetchColumn('SELECT COUNT(1) FROM orders WHERE (DATE(created_at) = ? OR DATE(scheduled_delivery) = ?) AND status != -1', [$this->currentDatetime->format('Y-m-d'), $this->currentDatetime->format('Y-m-d')]);
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