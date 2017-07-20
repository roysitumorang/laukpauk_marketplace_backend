<?php

namespace Application\Api\V2\Controllers;

use Application\Models\Order;
use Application\Models\OrderProduct;
use Application\Models\Role;
use Application\Models\User;
use Application\Models\Village;
use DateTime;
use IntlDateFormatter;
use Phalcon\Db;
use Phalcon\Exception;

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
				, Role::MERCHANT, $this->_premium_merchant ? $this->_premium_merchant->id : $this->_input->merchant_id, $this->_current_user->village->id];
			$params[0] .= ($this->_premium_merchant ? ' = 1' : ' IS NULL');
			$merchant   = $this->db->fetchOne(array_shift($params), Db::FETCH_OBJ, $params);
			if (!$merchant) {
				throw new Exception('Order Anda tidak valid!');
			}
			if ($this->_input->coupon_code) {
				$today = $this->currentDatetime->format('Y-m-d');
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
						a.code = ? AND
						a.user_id
QUERY
					,
					$this->_current_user->id,
					$today,
					$today,
					$this->_input->coupon_code,
				];
				$params[0] .= ($this->_premium_merchant ? ' = 1' : ' IS NULL') . ' GROUP BY a.id';
				$coupon     = $this->db->fetchOne(array_shift($params), Db::FETCH_OBJ, $params);
				if (!$coupon || ($coupon->multiple_use && $coupon->usage > 1)) {
					throw new Exception('Voucher tidak valid! Silahkan cek ulang atau kosongkan untuk melanjutkan pemesanan.');
				}
			}
			$order          = new Order;
			$order_products = [];
			$delivery_date  = new DateTime($this->_input->scheduled_delivery->date, $this->currentDatetime->getTimezone());
			$delivery_date->setTime($this->_input->scheduled_delivery->hour, 0, 0);
			$order->merchant_id        = $merchant->id;
			$order->name               = $this->_current_user->name;
			$order->mobile_phone       = $this->_current_user->mobile_phone;
			$order->address            = $this->_input->address;
			$order->village_id         = $this->_current_user->village_id;
			$order->original_bill      = 0;
			$order->scheduled_delivery = $delivery_date->format('Y-m-d H:i:s');
			$order->note               = $this->_input->note;
			$order->buyer_id           = $this->_current_user->id;
			$order->created_by         = $this->_current_user->id;
			foreach ($this->_input->items as $item) {
				$product                = $this->db->fetchOne('SELECT b.id, b.name, b.stock_unit, a.price, a.stock FROM user_product a JOIN products b ON a.product_id = b.id WHERE a.published = 1 AND b.published = 1 AND a.price > 0 AND a.stock > 0 AND a.user_id = ? AND a.id = ?', Db::FETCH_OBJ, [$merchant->id, $item->store_item_id]);
				$order_product             = new OrderProduct;
				$order_product->product_id = $product->id;
				$order_product->name       = $product->name;
				$order_product->price      = $product->price;
				$order_product->stock_unit = $product->stock_unit;
				$order_product->quantity   = min(max($item->quantity, 0), $product->stock);
				$order_product->created_by = $this->_current_user->id;
				$order->original_bill     += $order_product->quantity * $order_product->price;
				$order_products[]          = $order_product;
			}
			if ($order->original_bill < $merchant->minimum_purchase) {
				throw new Exception('Belanja minimal Rp. ' . number_format($merchant->minimum_purchase) . ' untuk dapat diproses!');
			}
			$order->final_bill = $order->original_bill;
			$order->discount   = 0;
			if ($coupon) {
				if ($coupon->minimum_purchase && $order->original_bill < $coupon->minimum_purchase) {
					throw new Exception('Voucher berlaku jika belanja minimal Rp. ' . number_format($coupon->minimum_purchase));
				}
				$order->coupon_id  = $coupon->id;
				$order->discount   = $coupon->discount_type == 1 ? $coupon->price_discount : ceil($coupon->price_discount * $order->original_bill / 100.0);
				$order->final_bill = $order->original_bill - $order->discount;
			}
			$order->shipping_cost = $merchant->shipping_cost;
			$order->final_bill   += $order->shipping_cost;
			$order->orderProducts = $order_products;
			if ($order->validation() && $order->create()) {
				if (!$this->_current_user->address) {
					$this->_current_user->update(['address' => $this->_input->address, 'updated_by' => $this->_current_user->id]);
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
		try {
			if (!$this->request->isPost() || $this->_current_user->role->name != 'Merchant') {
				throw new Exception('Request tidak valid!');
			}
			$order = $this->_current_user->getRelated('merchantOrders', [
				'status = 0 AND id = ?0',
				'bind' => [$id]
			])->getFirst();
			if (!$order) {
				throw new Exception('Order tidak ditemukan');
			}
			$order->updated_by = $this->_current_user->id;
			$order->complete();
			$this->_response['status'] = 1;
			throw new Exception('Order #' . $order->code . ' telah selesai, terima kasih');
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
	}

	function cancelAction($id) {
		try {
			if (!$this->request->isPost() || $this->_current_user->role->name != 'Merchant') {
				throw new Exception('Request tidak valid!');
			}
			$order = $this->_current_user->getRelated('merchantOrders', [
				'status = 0 AND id = ?0',
				'bind' => [$id]
			])->getFirst();
			if (!$order) {
				throw new Exception('Order tidak ditemukan');
			}
			if (!$this->_input->cancellation_reason) {
				throw new Exception('Alasan pembatalan harus diisi');
			}
			$order->updated_by = $this->_current_user->id;
			$order->cancel($this->_input->cancellation_reason);
			$this->_response['status'] = 1;
			throw new Exception('Order #' . $order->code . ' telah dicancel!');
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
	}

	function showAction($id) {
		if ($this->_current_user->role->name == 'Buyer') {
			$collection = 'buyerOrders';
		} else if ($this->_current_user->role->name == 'Merchant') {
			$collection = 'merchantOrders';
		}
		$order = $this->_current_user->getRelated($collection, ['Application\Models\Order.id = ?0', 'bind' => [$id]])->getFirst();
		if (!$order) {
			$this->_response['message'] = 'Pesanan tidak ditemukan!';
		} else {
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
			$this->_response['status']        = 1;
			$this->_response['data']['order'] = $payload;
		}
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}