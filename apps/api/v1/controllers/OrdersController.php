<?php

namespace Application\Api\V1\Controllers;

use Application\Models\Notification;
use Application\Models\NotificationTemplate;
use Application\Models\Order;
use Application\Models\OrderItem;
use Application\Models\ProductPrice;
use Application\Models\Role;
use Application\Models\User;
use Application\Models\Village;

class OrdersController extends ControllerBase {
	function indexAction() {
		$orders = [];
		foreach ($this->_access_token->user->buyer_orders as $order) {
			$items    = [];
			$village  = Village::findFirst($order->village_id);
			$merchant = User::findFirst($order->merchant_id);
			foreach ($order->items as $item) {
				$items[$item->id] = [
					'name'       => $item->name,
					'stock_unit' => $item->stock_unit,
					'unit_size'  => $item->unit_size,
					'unit_price' => $item->unit_price,
					'quantity'   => $item->quantity,
				];
			}
			$orders[$order->id] = [
				'code'               => $order->code,
				'status'             => $order->status,
				'name'               => $order->name,
				'phone'              => $order->phone,
				'address'            => $order->address,
				'village'            => $village->name,
				'subdistrict'        => $village->subdistrict->name,
				'final_bill'         => $order->final_bill,
				'original_bill'      => $order->original_bill,
				'estimated_delivery' => str_replace(' ', 'T', $order->estimated_delivery),
				'merchant'           => $merchant->company ?: $merchant->name,
				'items'              => $items,
			];
		}
		$this->_response['status']         = 1;
		$this->_response['data']['orders'] = $orders;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function createAction() {
		if (!$this->request->isPost() || !$this->_access_token->user) {
			$this->_response['message'] = 'Request tidak valid!';
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		if (!$this->_input->items) {
			$this->_response['message'] = 'Order item kosong!';
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$order         = new Order;
		$order_items   = [];
		$order->status = array_search('HOLD', ORDER::STATUS);
		do {
			$order->code = random_int(111111, 999999);
			if (!Order::findFirstByCode($order->code)) {
				break;
			}
		} while (1);
		$merchant                  = User::findFirst([
			'conditions' => 'status = :status: AND role_id = :role_id: AND id = :id:',
			'bind'       => [
				'status'  => array_search('ACTIVE', User::STATUS),
				'role_id' => Role::MERCHANT,
				'id'      => $this->_input->merchant_id,
			],
		]);
		$order->merchant_id        = $merchant->id;
		$order->name               = $this->_access_token->user->name;
		$order->phone              = $this->_access_token->user->phone;
		$order->address            = $this->_input->address;
		$order->village_id         = $this->_access_token->user->village_id;
		$order->original_bill      = 0;
		$order->estimated_delivery = $this->_input->estimated_delivery;
		$order->note               = $this->_input->note;
		$order->buyer_id           = $this->_access_token->user->id;
		$order->created_by         = $this->_access_token->user->id;
		foreach ($this->_input->items as $item) {
			$order_item              = new OrderItem;
			$price                   = ProductPrice::findFirst($item->product_price_id);
			$product                 = $price->product;
			$order_item->product_id  = $product->id;
			$order_item->name        = $product->name;
			$order_item->unit_price  = $price->value;
			$order_item->unit_size   = $price->unit_size;
			$order_item->stock_unit  = $product->stock_unit;
			$order_item->quantity    = $item->quantity;
			$order->original_bill   += $price->value * $item->quantity;
			$order_items[]           = $order_item;
		}
		$order->final_bill    = $order->original_bill;
		$order->items         = $order_items;
		if ($order->validation() && $order->create()) {
			$this->_response['status']      = 1;
			$this->_response['message']     = 'Pemesanan berhasil!';
			$admin_new_order_template       = NotificationTemplate::findFirstByName('admin new order');
			$admin_notification             = new Notification;
			$admin_notification->subject    = $admin_new_order_template->subject;
			$admin_notification->link       = $admin_new_order_template->url . $order->id;
			$admin_notification->created_by = $this->_access_token->user->id;
			$admins                      = User::find([
				'conditions' => 'role_id IN ({role_ids:array}) AND status = :status:',
				'bind'       => [
					'role_ids' => [Role::SUPER_ADMIN, Role::ADMIN],
					'status'   => array_search('ACTIVE', User::STATUS),
				],
			]);
			foreach ($admins as $admin) {
				$recipients[] = $admin;
			}
			$admin_notification->recipients    = $recipients;
			$admin_notification->create();
			$merchant_new_order_template       = NotificationTemplate::findFirstByName('api new order');
			$merchant_notification             = new Notification;
			$merchant_notification->subject    = $merchant_new_order_template->subject;
			$merchant_notification->link       = $merchant_new_order_template->url . $order->id;
			$merchant_notification->created_by = $this->_access_token->user->id;
			$merchant_notification->recipients = [$merchant];
			$merchant_notification->create();
		} else {
			$errors = [];
			foreach ($order->getMessages() as $error) {
				$errors[] = $error->getMessage();
			}
			$this->_response['message']        = 'Pemesanan tidak berhasil!';
			$this->_response['data']['errors'] = $errors;
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}