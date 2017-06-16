<?php

namespace Application\Api\V3\Controllers;

use Application\Models\Device;
use Application\Models\Order;
use Application\Models\OrderItem;
use Application\Models\Product;
use Application\Models\Role;
use Application\Models\ServiceArea;
use Application\Models\Setting;
use Application\Models\User;
use Application\Models\Village;
use DateTime;
use IntlDateFormatter;
use Phalcon\Db;
use Phalcon\Exception;
use stdClass;

class OrdersController extends ControllerBase {
	function indexAction() {
		$orders = [];
		$limit  = 10;
		if ($this->_current_user->role->name == 'Buyer') {
			$field = 'a.buyer_id';
		} else if ($this->_current_user->role->name == 'Merchant') {
			$field = 'a.merchant_id';
		}
		$date_formatter = new IntlDateFormatter(
			'id_ID',
			IntlDateFormatter::FULL,
			IntlDateFormatter::NONE,
			$this->currentDatetime->getTimezone(),
			IntlDateFormatter::GREGORIAN,
			'd MMM yyyy'
		);
		$page         = $this->dispatcher->getParam('page', 'int');
		$current_page = $page > 0 ? $page : 1;
		$offset       = ($current_page - 1) * $limit;
		$result       = $this->db->query(<<<QUERY
			SELECT
				a.id,
				a.code,
				a.status,
				a.final_bill,
				a.scheduled_delivery,
				b.company,
				b.address
			FROM
				orders a
				JOIN users b ON a.merchant_id = b.id
			WHERE {$field} = {$this->_current_user->id}
			ORDER BY a.id DESC
			LIMIT {$limit} OFFSET {$offset}
QUERY
		);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($order = $result->fetch()) {
			$schedule        = new DateTime($order->scheduled_delivery, $this->currentDatetime->getTimezone());
			$order->delivery = new stdClass;
			$order->merchant = new stdClass;
			if ($order->status == 1) {
				$order->delivery->status = 'Selesai';
			} else if ($order->status == -1) {
				$order->delivery->status = 'Dibatalkan';
			} else {
				$order->delivery->status = 'Sedang Diproses';
			}
			$order->delivery->day     = $date_formatter->format($schedule);
			$order->delivery->hour    = $schedule->format('G');
			$order->merchant->company = $order->company;
			$order->merchant->address = $order->address;
			unset($order->status, $order->company, $order->address);
			$orders[] = $order;
		}
		$this->_response['status'] = 1;
		$this->_response['data']   = ['orders' => $orders];
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
			if (!$this->_post->orders) {
				throw new Exception('Order item kosong!');
			}
			$orders = [];
			$total  = 0;
			$today  = $this->currentDatetime->format('Y-m-d');
			try {
				$delivery_date = new DateTime($this->_post->delivery->date, $this->currentDatetime->getTimezone());
			} catch (Exception $ex) {
				throw new Exception('Order Anda tidak valid!');
			}
			if (!filter_var($this->_post->delivery->hour, FILTER_VALIDATE_INT)) {
				throw new Exception('Order Anda tidak valid!');
			}
			$delivery_date->setTime($this->_post->delivery->hour, 0, 0);
			if ($this->_post->coupon_code) {
				$params = [<<<QUERY
					SELECT
						a.id,
						a.price_discount,
						a.discount_type,
						a.multiple_use,
						a.minimum_purchase,
						COUNT(1) AS usage
					FROM
						coupons a
						LEFT JOIN orders b ON a.id = b.coupon_id AND b.status = '1' AND b.buyer_id = ?
					WHERE
						a.status = '1' AND
						a.effective_date <= ? AND
						a.expiry_date > ? AND
						LOWER(a.code) = ? AND
						a.user_id
QUERY
					,
					$this->_current_user->id,
					$today,
					$today,
					strtolower($this->_post->coupon_code),
				];
				$params[0] .= ($this->_premium_merchant ? ' = 1' : ' IS NULL') . ' GROUP BY a.id';
				$coupon     = $this->db->fetchOne(array_shift($params), Db::FETCH_OBJ, $params);
				if (!$coupon || ($coupon->multiple_use && $coupon->usage > 1)) {
					throw new Exception('Order Anda tidak valid!');
				}
			}
			foreach ($this->_post->orders as $cart) {
				$merchant = $this->_premium_merchant ? $this->_premium_merchant : User::findFirst(['conditions' => 'status = 1 AND role_id = ?0 AND premium_merchant IS NULL AND id = ?1', 'bind' => [Role::MERCHANT, $cart->merchant_id]]);
				if (!$merchant) {
					throw new Exception('Order Anda tidak valid!');
				}
				$order                     = new Order;
				$order_items               = [];
				$order->merchant           = $merchant;
				$order->name               = $this->_post->delivery->name;
				$order->mobile_phone       = $this->_current_user->mobile_phone;
				$order->address            = $this->_post->delivery->address;
				$order->village_id         = $this->_current_user->village_id;
				$order->original_bill      = 0;
				$order->scheduled_delivery = $delivery_date->format('Y-m-d H:i:s');
				$order->note               = $cart->note;
				$order->buyer              = $this->_current_user;
				$order->created_by         = $this->_current_user->id;
				foreach ($cart->products as $item) {
					$product = Product::findFirst(['published = 1 AND price > 0 AND stock > 0 AND user_id = ?0 AND id = ?1', 'bind' => [$merchant->id, $item->id]]);
					if (!$product) {
						throw new Exception('Order Anda tidak valid');
					}
					$order_item             = new OrderItem;
					$order_item->product_id = $product->id;
					$order_item->name       = $product->name;
					$order_item->unit_price = $product->price;
					$order_item->stock_unit = $product->stock_unit;
					$order_item->quantity   = min($item->quantity, $product->stock);
					$order->original_bill  += $order_item->quantity * $product->price;
					$order_items[]          = $order_item;
				}
				$service_area     = ServiceArea::findFirst(['user_id = ?0 AND village_id = ?1', 'bind' => [$merchant->id, $this->_current_user->village->id]]);
				$minimum_purchase = $service_area && $service_area->minimum_purchase ? $service_area->minimum_purchase : ($merchant->minimum_purchase ?: Setting::findFirstByName('minimum_purchase')->value);
				if ($order->original_bill < $minimum_purchase) {
					throw new Exception('Order Anda tidak valid!');
				}
				$order->final_bill    = $order->original_bill;
				$order->discount      = 0;
				$order->shipping_cost = $merchant->shipping_cost ?? 0;
				$order->final_bill   += $order->shipping_cost;
				$order->items         = $order_items;
				if (!$order->validation()) {
					throw new Exception('Order Anda tidak valid!');
				}
				if (!$coupon) {
					$order->create();
					continue;
				}
				$total   += $order->final_bill;
				$orders[] = $order;
			}
			if ($coupon) {
				if ($total < $coupon->minimum_purchase) {
					throw new Exception('Order Anda tidak valid!');
				}
				$discount = $coupon->discount_type == 1 ? $coupon->price_discount : ceil($coupon->price_discount * $total / 100);
				foreach ($orders as $order) {
					if ($discount) {
						$order->coupon_id   = $coupon->id;
						$order->discount    = min($order->final_bill, $discount);
						$order->final_bill -= $order->discount;
						$discount           = max($discount - $order->discount, 0);
					}
					$order->create();
				}
			}
			if ($this->_post->device_token) {
				$device = Device::findFirstByToken($this->_post->device_token);
				if (!$device) {
					$device             = new Device;
					$device->token      = $this->_post->device_token;
					$device->user_id    = $this->_current_user->id;
					$device->created_by = $this->_current_user->id;
					$device->create();
				} else if ($device->user_id != $this->_current_user->id) {
					$device->user_id    = $this->_current_user->id;
					$device->updated_by = $this->_current_user->id;
					$device->update();
				}
			}
			$this->_response['status']  = 1;
			$this->_response['message'] = 'Terima kasih, order Anda segera kami proses.';
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
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
				'd MMM yyyy'
			);
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
					'day'    => $date_formatter->format($scheduled_delivery),
					'hour'   => $scheduled_delivery->format('G'),
				],
				'note'          => $order->note,
				'merchant'      => [
					'company' => $merchant->company,
					'address' => $merchant->address,
				],
				'items'         => $items,
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