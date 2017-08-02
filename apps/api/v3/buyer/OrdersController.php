<?php

namespace Application\Api\V3\Buyer;

use Application\Models\Device;
use Application\Models\Order;
use Application\Models\OrderProduct;
use Application\Models\Release;
use Application\Models\Role;
use Application\Models\Setting;
use Application\Models\User;
use Application\Models\Village;
use DateTime;
use Phalcon\Db;
use Exception;

class OrdersController extends ControllerBase {
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
				a.scheduled_delivery,
				b.company,
				b.address
			FROM
				orders a
				JOIN users b ON a.merchant_id = b.id
			WHERE a.buyer_id = {$this->_current_user->id}
			ORDER BY a.id DESC
			LIMIT {$limit} OFFSET {$offset}
QUERY
		);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($order = $result->fetch()) {
			$schedule        = new DateTime($order->scheduled_delivery, $this->currentDatetime->getTimezone());
			$order->delivery = [
				'day'    => $this->_date_formatter->format($schedule),
				'hour'   => $schedule->format('G'),
				'status' => call_user_func(function() use($order) {
					if ($order->status == 1) {
						return 'Selesai';
					} else if ($order->status == -1) {
						return 'Dibatalkan';
					}
					return 'Sedang Diproses';
				}),
			];
			$order->merchant = [
				'company' => $order->company,
				'address' => $order->address,
			];
			unset($order->status, $order->company, $order->address);
			$orders[] = $order;
		}
		$this->_response['status']                          = 1;
		$this->_response['data']['orders']                  = $orders;
		$this->_response['data']['total_new_notifications'] = $this->_current_user->totalNewNotifications();
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function checkAction() {
		try {
			if (!$this->request->isPost()) {
				throw new Exception('Request tidak valid!');
			}
			if (!$this->_post->orders) {
				throw new Exception('Order item kosong!');
			}
			$total            = 0;
			$minimum_purchase = Setting::findFirstByName('minimum_purchase')->value;
			foreach ($this->_post->orders as $cart) {
				$params = [<<<QUERY
					SELECT
						a.id,
						b.shipping_cost,
						a.minimum_purchase
					FROM
						users a
						JOIN coverage_area b ON a.id = b.user_id
					WHERE
						a.status = 1 AND
						a.role_id = ? AND
						a.id = ? AND
						b.village_id = ? AND
						a.premium_merchant
QUERY
					, Role::MERCHANT, $cart->merchant_id, $this->_current_user->village->id];
				$params[0] .= $this->_premium_merchant ? ' = 1' : ' IS NULL';
				$merchant   = $this->db->fetchOne(array_shift($params), Db::FETCH_OBJ, $params);
				if (!$merchant) {
					throw new Exception('Order Anda tidak valid!');
				}
				$purchase = 0;
				foreach ($cart->products as $item) {
					$product = $this->db->fetchOne('SELECT b.id, b.name, b.stock_unit, a.price, a.stock FROM user_product a JOIN products b ON a.product_id = b.id WHERE a.published = 1 AND b.published = 1 AND a.price > 0 AND a.stock > 0 AND a.user_id = ? AND a.id = ?', Db::FETCH_OBJ, [$merchant->id, $item->id]);
					if (!$product) {
						throw new Exception('Order Anda tidak valid');
					}
					$purchase += min(max($item->quantity, 0), $product->stock) * $product->price;
				}
				if ($purchase < $merchant->minimum_purchase) {
					throw new Exception('Order Anda tidak valid!');
				}
				$total += $purchase;
			}
			if (!$this->_premium_merchant && $total < $minimum_purchase) {
				throw new Exception('Belanja minimal Rp. ' . number_format($minimum_purchase) . ' untuk dapat diproses!');
			}
			$this->_response['status'] = 1;
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
	}

	function createAction() {
		try {
			if (!$this->request->isPost()) {
				throw new Exception('Request tidak valid!');
			}
			if (!$this->_post->orders) {
				throw new Exception('Order item kosong!');
			}
			$orders           = [];
			$total            = 0;
			$today            = $this->currentDatetime->format('Y-m-d');
			$coupon           = null;
			$discount         = 0;
			$minimum_purchase = Setting::findFirstByName('minimum_purchase')->value;
			try {
				$delivery_date = new DateTime($this->_post->delivery->date, $this->currentDatetime->getTimezone());
			} catch (Exception $ex) {
				throw new Exception('Order Anda tidak valid!');
			}
			if (!filter_var($this->_post->delivery->hour, FILTER_VALIDATE_INT)) {
				throw new Exception('Order Anda tidak valid!');
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
			$delivery_date->setTime($this->_post->delivery->hour, 0, 0);
			if (property_exists($this->_post, 'coupon_code') && $this->_post->coupon_code) {
				$params = [<<<QUERY
					SELECT
						a.id,
						a.price_discount,
						a.discount_type,
						a.multiple_use,
						a.minimum_purchase,
						d.version AS minimum_version,
						a.maximum_usage,
						COUNT(DISTINCT b.id) AS personal_usage,
						COUNT(DISTINCT c.id) AS total_usage
					FROM
						coupons a
						LEFT JOIN orders b ON a.id = b.coupon_id AND b.status != '-1' AND b.buyer_id = ?
						LEFT JOIN orders c ON a.id = c.coupon_id AND c.status != '-1'
						LEFT JOIN releases d ON a.release_id = d.id
					WHERE
						a.status = '1' AND
						a.effective_date <= ? AND
						a.expiry_date > ? AND
						a.code = ? AND
						a.user_id
QUERY
					,
					$this->_current_user->id,
					$today,
					$today,
					$this->_post->coupon_code,
				];
				$params[0] .= ($this->_premium_merchant ? ' = 1' : ' IS NULL') . ' GROUP BY a.id, d.version';
				$coupon     = $this->db->fetchOne(array_shift($params), Db::FETCH_OBJ, $params);
				if (!$coupon) {
					throw new Exception('Order Anda tidak valid!');
				} else if ($coupon->maximum_usage && $coupon->total_usage >= $coupon->maximum_usage) {
					throw new Exception('Pemakaian voucher udah melebihi batas maksimal!');
				} else if ($coupon->minimum_version && (!$this->_post->app_version || !Release::findFirst(['type = ?0 AND version = ?1', 'bind' => ['buyer', $this->_post->app_version]]) || $this->_post->app_version < $coupon->minimum_version)) {
					throw new Exception('Voucher berlaku untuk versi minimal ' . $coupon->minimum_version . '! Silahkan upgrade aplikasi Anda.');
				} else if (!$coupon->multiple_use && $coupon->personal_usage > 1) {
					throw new Exception('Voucher cuma berlaku untuk sekali pemakaian!');
				}
			}
			foreach ($this->_post->orders as $cart) {
				$params = [<<<QUERY
					SELECT
						a.id,
						b.shipping_cost,
						a.minimum_purchase
					FROM
						users a
						JOIN coverage_area b ON a.id = b.user_id
					WHERE
						a.status = 1 AND
						a.role_id = ? AND
						a.id = ? AND
						b.village_id = ? AND
						a.premium_merchant
QUERY
					, Role::MERCHANT, $cart->merchant_id, $this->_current_user->village->id];
				$params[0] .= $this->_premium_merchant ? ' = 1' : ' IS NULL';
				$merchant   = $this->db->fetchOne(array_shift($params), Db::FETCH_OBJ, $params);
				if (!$merchant) {
					throw new Exception('Order Anda tidak valid!');
				}
				$order                     = new Order;
				$order_products            = [];
				$order->merchant_id        = $merchant->id;
				$order->name               = $this->_post->delivery->name;
				$order->mobile_phone       = $this->_current_user->mobile_phone;
				$order->address            = $this->_post->delivery->address;
				$order->village_id         = $this->_current_user->village_id;
				$order->original_bill      = 0;
				$order->scheduled_delivery = $delivery_date->format('Y-m-d H:i:s');
				$order->note               = property_exists($cart, 'note') ? $cart->note : null;
				$order->buyer_id           = $this->_current_user->id;
				$order->created_by         = $this->_current_user->id;
				$order->setTransaction($this->transactionManager->get());
				foreach ($cart->products as $item) {
					$product = $this->db->fetchOne('SELECT b.id, b.name, b.stock_unit, a.price, a.stock FROM user_product a JOIN products b ON a.product_id = b.id WHERE a.published = 1 AND b.published = 1 AND a.price > 0 AND a.stock > 0 AND a.user_id = ? AND a.id = ?', Db::FETCH_OBJ, [$merchant->id, $item->id]);
					if (!$product) {
						throw new Exception('Order Anda tidak valid');
					}
					$order_product             = new OrderProduct;
					$order_product->product_id = $product->id;
					$order_product->name       = $product->name;
					$order_product->price      = $product->price;
					$order_product->stock_unit = $product->stock_unit;
					$order_product->quantity   = min(max($item->quantity, 0), $product->stock);
					$order_product->created_by = $this->_current_user->id;
					$order->original_bill     += $order_product->quantity * $product->price;
					$order_products[]          = $order_product;
				}
				if ($order->original_bill < $merchant->minimum_purchase) {
					throw new Exception('Order Anda tidak valid!');
				}
				$order->final_bill    = $order->original_bill;
				$order->discount      = 0;
				$order->shipping_cost = $merchant->shipping_cost;
				$order->orderProducts = $order_products;
				if (!$order->validation()) {
					throw new Exception('Order Anda tidak valid!');
				}
				$total   += $order->final_bill;
				$orders[] = $order;
			}
			if (!$this->_premium_merchant && $total < $minimum_purchase) {
				throw new Exception('Belanja minimal Rp. ' . number_format($minimum_purchase) . ' untuk dapat diproses!');
			}
			if ($coupon) {
				if ($total < $coupon->minimum_purchase) {
					throw new Exception('Order Anda tidak valid!');
				}
				$discount = $coupon->discount_type == 1 ? $coupon->price_discount : ceil($coupon->price_discount * $total / 100);
			}
			foreach ($orders as $order) {
				if ($discount) {
					$order->coupon_id = $coupon->id;
				}
				$order->discount   = min($order->final_bill, $discount);
				$order->final_bill = $order->final_bill - $order->discount + $order->shipping_cost;
				$discount          = max($discount - $order->discount, 0);
				$order->create();
			}
			$this->_response['status'] = 1;
			throw new Exception('Terima kasih, order Anda segera kami proses.');
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
	}

	function showAction($id) {
		try {
			$order = $this->_current_user->getRelated('buyerOrders', ['Application\Models\Order.id = ?0 OR Application\Models\Order.code = ?1', 'bind' => [$id, $id]])->getFirst();
			if (!$order) {
				throw new Exception('Pesanan tidak ditemukan!');
			}
			$items    = [];
			$village  = Village::findFirst($order->village_id);
			$merchant = $this->_premium_merchant ?: User::findFirst($order->merchant_id);
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
				'note'     => $order->note,
				'items'    => $items,
				'merchant' => [
					'company' => $merchant->company,
					'address' => $merchant->address,
				],
			];
			if ($order->status == -1) {
				$payload['cancellation_reason'] = $order->cancellation_reason;
			}
			$this->_response['status']                          = 1;
			$this->_response['data']['order']                   = $payload;
			$this->_response['data']['total_new_notifications'] = $this->_current_user->totalNewNotifications();
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
	}
}