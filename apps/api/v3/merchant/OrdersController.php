<?php

namespace Application\Api\V3\Merchant;

use Application\Models\Village;
use DateTime;
use IntlDateFormatter;
use Phalcon\Db;
use Phalcon\Exception;
use stdClass;

class OrdersController extends ControllerBase {
	private $_date_formatter;

	function initialize() {
		parent::initialize();
		$this->_date_formatter =  new IntlDateFormatter(
			'id_ID',
			IntlDateFormatter::FULL,
			IntlDateFormatter::NONE,
			$this->currentDatetime->getTimezone(),
			IntlDateFormatter::GREGORIAN,
			'd MMM yyyy'
		);
	}

	function indexAction() {
		$orders       = [];
		$limit        = 10;
		$page         = $this->dispatcher->getParam('page', 'int');
		$current_page = $page > 0 ? $page : 1;
		$offset       = ($current_page - 1) * $limit;
		$result       = $this->db->query(<<<QUERY
			SELECT
				a.id,
				a.code,
				a.status,
				a.final_bill,
				a.scheduled_delivery
			FROM
				orders a
				JOIN users b ON a.merchant_id = b.id
			WHERE a.merchant_id = {$this->_current_user->id}
			ORDER BY a.id DESC
			LIMIT {$limit} OFFSET {$offset}
QUERY
		);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($order = $result->fetch()) {
			$schedule        = new DateTime($order->scheduled_delivery, $this->currentDatetime->getTimezone());
			$order->delivery = new stdClass;
			if ($order->status == 1) {
				$order->delivery->status = 'Selesai';
			} else if ($order->status == -1) {
				$order->delivery->status = 'Dibatalkan';
			} else {
				$order->delivery->status = 'Sedang Diproses';
			}
			$order->delivery->day  = $this->_date_formatter->format($schedule);
			$order->delivery->hour = $schedule->format('G');
			unset($order->status);
			$orders[] = $order;
		}
		$this->_response['status'] = 1;
		$this->_response['data']   = [
			'orders'                  => $orders,
			'total_new_orders'        => $this->_current_user->totalNewOrders(),
			'total_new_notifications' => $this->_current_user->totalNewNotifications(),
		];
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function completeAction($id) {
		try {
			if (!$this->request->isPost()) {
				throw new Exception('Request tidak valid!');
			}
			$order = $this->_current_user->getRelated('merchantOrders', [
				'status = 0 AND (id = ?0 OR code = ?1)',
				'bind' => [$id, $id],
			])->getFirst();
			if (!$order) {
				throw new Exception('Pesanan tidak ditemukan!');
			}
			$order->complete();
			$this->_response['status'] = 1;
			throw new Exception("Pesanan #{$order->code} telah selesai, terima kasih.");
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->_response['data'] = [
				'total_new_orders'        => $this->_current_user->totalNewOrders(),
				'total_new_notifications' => $this->_current_user->totalNewNotifications(),
			];
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
	}

	function cancelAction($id) {
		try {
			if (!$this->request->isPost()) {
				throw new Exception('Request tidak valid!');
			}
			if (!$this->_post->cancellation_reason) {
				throw new Exception('Alasan pembatalan harus diisi.');
			}
			$order = $this->_current_user->getRelated('merchantOrders', [
				'status = 0 AND (id = ?0 OR code = ?1)',
				'bind' => [$id, $id],
			])->getFirst();
			if (!$order) {
				throw new Exception('Pesanan tidak ditemukan!');
			}
			$order->cancel($this->_post->cancellation_reason);
			$this->_response['status'] = 1;
			throw new Exception("Pesanan #{$order->code} berhasil dibatalkan!");
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->_response['data'] = [
				'total_new_orders'        => $this->_current_user->totalNewOrders(),
				'total_new_notifications' => $this->_current_user->totalNewNotifications(),
			];
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
	}

	function showAction($id) {
		$order = $this->_current_user->getRelated('merchantOrders', ['Application\Models\Order.id = ?0 OR Application\Models\Order.code = ?1', 'bind' => [$id, $id]])->getFirst();
		if (!$order) {
			$this->_response['message'] = 'Pesanan tidak ditemukan!';
		} else {
			$items   = [];
			$village = Village::findFirst($order->village_id);
			foreach ($order->orderProducts as $item) {
				$items[$item->id] = [
					'name'       => $item->name,
					'stock_unit' => $item->stock_unit,
					'unit_price' => $item->price,
					'quantity'   => $item->quantity,
				];
			}
			$scheduled_delivery = new DateTime($order->scheduled_delivery, $this->currentDatetime->getTimezone());
			$payload            = [
				'code'          => $order->code,
				'status'        => $order->status,
				'name'          => $order->name,
				'mobile_phone'  => $order->mobile_phone,
				'address'       => $order->address,
				'village'       => $village->name,
				'subdistrict'   => $village->subdistrict->name,
				'city'          => $village->subdistrict->city->name,
				'province'      => $village->subdistrict->city->province->name,
				'final_bill'    => $order->final_bill,
				'discount'      => $order->discount,
				'original_bill' => $order->original_bill,
				'shipping_cost' => $order->shipping_cost,
				'delivery'      => [
					'status' => call_user_func(function() use($order) {
						if ($order->status == 1) {
							return 'Selesai';
						} else if ($order->status == -1) {
							return 'Dibatalkan';
						}
						return 'Sedang Diproses';
					}),
					'day'  => $this->_date_formatter->format($scheduled_delivery),
					'hour' => $scheduled_delivery->format('G'),
				],
				'note'  => $order->note,
				'items' => $items,
			];
			if ($order->status == -1) {
				$payload['cancellation_reason'] = $order->cancellation_reason;
			}
			$this->_response['status'] = 1;
			$this->_response['data']   = [
				'order'                   => $payload,
				'total_new_orders'        => $this->_current_user->totalNewOrders(),
				'total_new_notifications' => $this->_current_user->totalNewNotifications(),
			];
		}
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}