<?php

namespace Application\Api\V1\Controllers;

use Application\Models\Order;
use Application\Models\OrderItem;
use Application\Models\ProductPrice;
use Application\Models\Role;
use Application\Models\User;

class OrdersController extends ControllerBase {
	function index() {}

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
		$order->merchant_id = User::findFirst([
			'conditions' => 'status = :status: AND role_id = :role_id: AND id = :id:',
			'bind'       => [
				'status'  => array_search('ACTIVE', User::STATUS),
				'role_id' => Role::MERCHANT,
				'id'      => $this->_input->merchant_id,
			],
		])->id;
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
			$order->original_bill   += $price->value * $item->quantity / $price->unit_size;
			$order_items[]           = $order_item;
		}
		$order->final_bill    = $order->original_bill;
		$order->items         = $order_items;
		if ($order->validation() && $order->create()) {
			$this->_response['message'] = 'Pemesanan berhasil!';
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