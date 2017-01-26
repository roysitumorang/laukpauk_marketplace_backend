<?php

namespace Application\Api\V1\Controllers;

use Application\Models\Order;
use Application\Models\OrderItem;
use Application\Models\ProductPrice;
use Application\Models\Role;
use Application\Models\User;
use Application\Models\Village;
use DateTime;
use Exception;
use Phalcon\Db;

class OrdersController extends ControllerBase {
	function indexAction() {
		$orders = [];
		$limit  = 10;
		if ($this->_current_user->role->name == 'Buyer') {
			$field = 'buyer_id';
		} else if ($this->_current_user->role->name == 'Merchant') {
			$field = 'merchant_id';
		}
		$total_pages  = ceil($this->db->fetchColumn("SELECT COUNT(1) FROM orders WHERE {$field} = {$this->_current_user->id}") / $limit);
		$page         = $this->dispatcher->getParam('page', 'int');
		$current_page = $page > 0 && $page <= $total_pages ? $page : 1;
		$offset       = ($current_page - 1) * $limit;
		foreach ($this->db->fetchAll("SELECT id, code, status, final_bill, scheduled_delivery FROM orders WHERE {$field} = {$this->_current_user->id} ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}", Db::FETCH_OBJ) as $order) {
			$orders[] = $order;
		}
		$this->_response['status'] = 1;
		$this->_response['data']   = [
			'orders'       => $orders,
			'total_pages'  => $total_pages,
			'current_page' => $current_page,
		];
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function createAction() {
		try {
			if (!$this->request->isPost()) {
				throw new Exception('Request tidak valid!');
			}
			if ($this->_current_user->role->name != 'Buyer') {
				throw new Exception('Hanya pembeli yang bisa melakukan pemesanan!');
			}
			if (!$this->_input->items) {
				throw new Exception('Order item kosong!');
			}
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$order       = new Order;
		$order_items = [];
		$merchant    = User::findFirst(['conditions' => 'status = 1 AND role_id = ?0 AND id = ?1', 'bind' => [
			Role::MERCHANT,
			$this->_input->merchant_id
		]]);
		$delivery_date = new DateTime($this->_input->scheduled_delivery->date, $this->currentDatetime->getTimezone());
		$delivery_date->setTime($this->_input->scheduled_delivery->hour, 0, 0);
		$order->merchant           = $merchant;
		$order->name               = $this->_current_user->name;
		$order->mobile_phone       = $this->_current_user->mobile_phone;
		$order->address            = $this->_input->address;
		$order->village_id         = $this->_current_user->village_id;
		$order->original_bill      = 0;
		$order->scheduled_delivery = $delivery_date->format('Y-m-d H:i:s');
		$order->note               = $this->_input->note;
		$order->buyer              = $this->_current_user;
		$order->created_by         = $this->_current_user->id;
		foreach ($this->_input->items as $item) {
			$order_item              = new OrderItem;
			$price                   = ProductPrice::findFirst($item->product_price_id);
			$product                 = $price->product;
			$order_item->product_id  = $product->id;
			$order_item->name        = $product->name;
			$order_item->unit_price  = $price->value;
			$order_item->stock_unit  = $product->stock_unit;
			$order_item->quantity    = $item->quantity;
			$order->original_bill   += $item->quantity * $price->value;
			$order_items[]           = $order_item;
		}
		$order->final_bill = $order->original_bill;
		$order->items      = $order_items;
		if ($order->validation() && $order->create()) {
			if (!$this->_current_user->address) {
				$this->_current_user->update(['address' => $this->_input->address]);
			}
			$this->_response['status']  = 1;
			$this->_response['message'] = 'Pemesanan berhasil!';
		} else {
			$errors = [];
			foreach ($order->getMessages() as $error) {
				$errors[] = $error->getMessage();
			}
			$this->_response['message'] = implode('<br>', $errors);
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function completeAction($id) {
		if (!$this->request->isPost()) {
			$this->_response['message'] = 'Request tidak valid!';
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$order = $this->_current_user->getRelated('merchant_orders', [
			'status = 0 AND id = ?0',
			'bind' => [$id]
		])->getFirst();
		if ($order) {
			$order->complete();
			$this->_response['status']  = 1;
			$this->_response['message'] = 'Order #' . $order->code . ' telah selesai, terima kasih';
		} else {
			$this->_response['message'] = 'Order tidak ditemukan';
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function cancelAction($id) {
		if (!$this->request->isPost()) {
			$this->_response['message'] = 'Request tidak valid!';
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$order = $this->_current_user->getRelated('merchant_orders', [
			'status = 0 AND id = ?0',
			'bind' => [$id]
		])->getFirst();
		if ($order) {
			$order->cancel();
			$this->_response['status']  = 1;
			$this->_response['message'] = 'Order #' . $order->code . ' telah dicancel!';
		} else {
			$this->_response['message'] = 'Order tidak ditemukan';
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function showAction($id) {
		if ($this->_current_user->role->name == 'Buyer') {
			$collection = 'buyer_orders';
		} else if ($this->_current_user->role->name == 'Merchant') {
			$collection = 'merchant_orders';
		}
		$order = $this->_current_user->getRelated($collection, ['Application\Models\Order.id = ?0', 'bind' => [$id]])->getFirst();
		if (!$order) {
			$this->_response['message'] = 'Pesanan tidak ditemukan!';
		} else {
			$items    = [];
			$village  = Village::findFirst($order->village_id);
			$merchant = User::findFirst($order->merchant_id);
			foreach ($order->items as $item) {
				$items[$item->id] = [
					'name'       => $item->name,
					'stock_unit' => $item->stock_unit,
					'unit_price' => $item->unit_price,
					'quantity'   => $item->quantity,
				];
			}
			$payload = [
				'code'               => $order->code,
				'status'             => $order->status,
				'name'               => $order->name,
				'mobile_phone'       => $order->mobile_phone,
				'address'            => $order->address,
				'village'            => $village->name,
				'subdistrict'        => $village->subdistrict->name,
				'final_bill'         => $order->final_bill,
				'original_bill'      => $order->original_bill,
				'scheduled_delivery' => str_replace(' ', 'T', $order->scheduled_delivery),
				'note'               => $order->note,
				'merchant'           => $merchant->company ?: $merchant->name,
				'items'              => $items,
			];
			$this->_response['status']        = 1;
			$this->_response['data']['order'] = $payload;
		}
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}