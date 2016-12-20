<?php

namespace Application\Api\V1\Controllers;

use Application\Models\Order;
use Application\Models\OrderItem;
use Application\Models\ProductPrice;
use Application\Models\Role;
use Application\Models\User;
use Application\Models\Village;

class OrdersController extends ControllerBase {
	function indexAction() {
		$orders = [];
		foreach ($this->_current_user->buyer_orders as $order) {
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
				'mobile_phone'       => $order->mobile_phone,
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
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function createAction() {
		if (!$this->request->isPost()) {
			$this->_response['message'] = 'Request tidak valid!';
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		if (!$this->_input->items) {
			$this->_response['message'] = 'Order item kosong!';
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$order       = new Order;
		$order_items = [];
		$merchant    = User::findFirst(['conditions' => 'status = 1 AND role_id = ?0 AND id = ?1', 'bind' => [
			Role::MERCHANT,
			$this->_input->merchant_id
		]]);
		$order->merchant           = $merchant;
		$order->name               = $this->_current_user->name;
		$order->mobile_phone       = $this->_current_user->mobile_phone;
		$order->address            = $this->_input->address;
		$order->village_id         = $this->_current_user->village_id;
		$order->original_bill      = 0;
		$order->estimated_delivery = $this->_input->estimated_delivery;
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
			$order_item->unit_size   = $product->unit_size;
			$order_item->stock_unit  = $product->stock_unit;
			$order_item->quantity    = $item->quantity;
			$order->original_bill   += $item->quantity * $price->value;
			$order_items[]           = $order_item;
		}
		$order->final_bill = $order->original_bill;
		$order->items      = $order_items;
		if ($order->validation() && $order->create()) {
			$this->_response['status']  = 1;
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