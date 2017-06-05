<?php

namespace Application\Api\V3\Controllers;

use Application\Models\Coupon;
use Application\Models\Order;
use Application\Models\OrderItem;
use Application\Models\StoreItem;
use Application\Models\Role;
use Application\Models\ServiceArea;
use Application\Models\Setting;
use Application\Models\User;
use Application\Models\Village;
use DateTime;
use Exception;
use IntlDateFormatter;
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
			if (!$this->_post->items) {
				throw new Exception('Order item kosong!');
			}
			if ($this->_premium_merchant) {
				$merchant = $this->_premium_merchant;
			} else {
				$merchant = User::findFirst(['conditions' => 'status = 1 AND role_id = ?0 AND id = ?1', 'bind' => [
					Role::MERCHANT,
					$this->_post->merchant_id
				]]);
			}
			if ($this->_post->coupon_code) {
				$current_date = $this->currentDatetime->format('Y-m-d');
				$coupon       = Coupon::findFirst(['status = 1 AND code = ?0 AND effective_date <= ?1 AND expiry_date > ?2', 'bind' => [
					$this->_post->coupon_code,
					$current_date,
					$current_date,
				]]);
				if (!$coupon ||
					(!empty($coupon->users) && empty($coupon->getRelated('users', ['user_id IN ({ids:array})', 'bind' => ['ids' => [$this->_current_user->id, $merchant->id]]]))) ||
					($coupon->usage == array_search('Sekali Pakai', Coupon::USAGE_TYPES) && $this->db->fetchColumn('SELECT COUNT(1) FROM orders WHERE buyer_id = ? AND coupon_id = ?', [$this->_current_user->id, $coupon->id]))
					) {
					throw new Exception('Voucher tidak valid! Silahkan cek ulang atau kosongkan untuk melanjutkan pemesanan.');
				}
			}
			$order         = new Order;
			$order_items   = [];
			$delivery_date = new DateTime($this->_post->scheduled_delivery->date, $this->currentDatetime->getTimezone());
			$delivery_date->setTime($this->_post->scheduled_delivery->hour, 0, 0);
			$order->merchant           = $merchant;
			$order->name               = $this->_current_user->name;
			$order->mobile_phone       = $this->_current_user->mobile_phone;
			$order->address            = $this->_post->address;
			$order->village_id         = $this->_current_user->village_id;
			$order->original_bill      = 0;
			$order->scheduled_delivery = $delivery_date->format('Y-m-d H:i:s');
			$order->note               = $this->_post->note;
			$order->buyer              = $this->_current_user;
			$order->created_by         = $this->_current_user->id;
			foreach ($this->_post->items as $item) {
				$store_item             = StoreItem::findFirst(['published = 1 AND price > 0 AND stock > 0 AND user_id = ?0 AND id = ?1', 'bind' => [$merchant->id, $item->product_price_id ?: $item->store_item_id]]);
				$order_item             = new OrderItem;
				$order_item->product_id = $store_item->product->id;
				$order_item->name       = $store_item->product->name;
				$order_item->unit_price = $store_item->price;
				$order_item->stock_unit = $store_item->product->stock_unit;
				$order_item->quantity   = min($item->quantity, $store_item->stock);
				$order->original_bill  += $order_item->quantity * $order_item->unit_price;
				$order_items[]          = $order_item;
			}
			$service_area     = ServiceArea::findFirst(['user_id = ?0 AND village_id = ?1', 'bind' => [$merchant->id, $this->_current_user->village->id]]);
			$minimum_purchase = $service_area && $service_area->minimum_purchase ? $service_area->minimum_purchase : ($merchant->minimum_purchase ?: Setting::findFirstByName('minimum_purchase')->value);
			if ($order->original_bill < $minimum_purchase) {
				throw new Exception('Belanja minimal Rp. ' . number_format($minimum_purchase) . ' untuk dapat diproses!');
			}
			$order->final_bill = $order->original_bill;
			$order->discount   = 0;
			if ($coupon) {
				if ($coupon->minimum_purchase && $order->original_bill < $coupon->minimum_purchase) {
					throw new Exception('Voucher berlaku jika belanja minimal Rp. ' . number_format($coupon->minimum_purchase));
				}
				$order->coupon     = $coupon;
				$order->discount   = $coupon->discount_type == 1 ? $coupon->discount_amount : ceil($coupon->discount_amount * $order->original_bill / 100.0);
				$order->final_bill = $order->original_bill - $order->discount;
			}
			$order->shipping_cost = $merchant->shipping_cost ?? 0;
			$order->final_bill   += $order->shipping_cost;
			$order->items         = $order_items;
			if ($order->validation() && $order->create()) {
				if (!$this->_current_user->address) {
					$this->_current_user->update(['address' => $this->_post->address]);
				}
				$this->_response['status']        = 1;
				$this->_response['data']['order'] = ['id' => $order->id];
				throw new Exception('Pemesanan berhasil!');
			}
			$errors = [];
			foreach ($order->getMessages() as $error) {
				$errors[] = $error->getMessage();
			}
			throw new Exception(implode('<br>', $errors));
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
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
			$order->cancel($this->_post->cancellation_reason);
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
			$merchant = $this->_premium_merchant ?: User::findFirst($order->merchant_id);
			foreach ($order->items as $item) {
				$items[$item->id] = [
					'name'       => $item->name,
					'stock_unit' => $item->stock_unit,
					'unit_price' => $item->unit_price,
					'quantity'   => $item->quantity,
				];
			}
			$date_formatter = new IntlDateFormatter(
				'id_ID',
				IntlDateFormatter::FULL,
				IntlDateFormatter::NONE,
				$this->currentDatetime->getTimezone(),
				IntlDateFormatter::GREGORIAN,
				'EEEE, d MMM yyyy'
			);
			$scheduled_delivery = new DateTime($order->scheduled_delivery, $this->currentDatetime->getTimezone());
			$payload            = [
				'code'               => $order->code,
				'status'             => $order->status,
				'name'               => $order->name,
				'mobile_phone'       => $order->mobile_phone,
				'address'            => $order->address,
				'village'            => $village->name,
				'subdistrict'        => $village->subdistrict->name,
				'city'               => $village->subdistrict->city->name,
				'province'           => $village->subdistrict->city->province->name,
				'final_bill'         => $order->final_bill,
				'discount'           => $order->discount,
				'original_bill'      => $order->original_bill,
				'shipping_cost'      => $order->shipping_cost,
				'scheduled_delivery' => [
					'date' => $date_formatter->format($scheduled_delivery),
					'hour' => $scheduled_delivery->format('H:i'),
				],
				'note'               => $order->note,
				'merchant'           => $merchant->company ?: $merchant->name,
				'items'              => $items,
			];
			if ($order->status == -1) {
				$payload['cancellation_reason'] = $order->cancellation_reason;
			}
			if ($coupon = $order->coupon) {
				$payload['discount'] = $coupon->discount_type == 1
							? $coupon->discount_amount
							: ($order->original_bill * $coupon->discount_amount / 100);
			}
			$this->_response['status']        = 1;
			$this->_response['data']['order'] = $payload;
		}
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}