<?php

namespace Application\Api\V3\Controllers;

use Application\Models\Role;
use Error;
use Phalcon\Db;

class CouponsController extends ControllerBase {
	function validateAction() {
		$discounts = [];
		$today     = $this->currentDatetime->format('Y-m-d');
		try {
			if (!$this->request->isOptions() || $this->_current_user->role->name != 'Buyer') {
				throw new Error('Request tidak valid!');
			}
			foreach ($this->_server->orders as $merchant_id => $order) {
				if (!$order->coupon_code) {
					continue;
				}
				$params = [<<<QUERY
					SELECT
						a.id,
						a.company,
						COALESCE(a.shipping_cost, 1) AS shipping_cost,
						COALESCE(b.minimum_purchase, a.minimum_purchase, c.value::int) AS minimum_purchase
					FROM
						users a
						JOIN service_areas b ON a.id = b.user_id
						JOIN settings c ON c.name = 'minimum_purchase'
					WHERE
						a.status = 1 AND
						a.role_id = ? AND
						a.id = ? AND
						b.village_id = ? AND
						a.premium_merchant
QUERY
					, Role::MERCHANT, $merchant_id, $this->_current_user->village_id];
				$params[0] .= ($this->_premium_merchant ? ' = 1' : ' IS NULL');
				$merchant   = $this->db->fetchOne(array_shift($params), Db::FETCH_OBJ, $params);
				if (!$merchant) {
					throw new Error("Kode voucher {$order->coupon_code} tidak valid! Silahkan cek lagi atau kosongkan untuk melanjutkan pemesanan.");
				}
				$params = [<<<QUERY
					SELECT
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
					strtolower($order->coupon_code),
				];
				$params[0] .= ($this->_premium_merchant ? ' = 1' : ' IS NULL') . ' GROUP BY a.id';
				$coupon     = $this->db->fetchOne(array_shift($params), Db::FETCH_OBJ, $params);
				if (!$coupon || ($coupon->multiple_use && $coupon->usage > 1)) {
					throw new Error("Kode voucher {$order->coupon_code} tidak valid! Silahkan cek lagi atau kosongkan untuk melanjutkan pemesanan.");
				}
				$original_bill = 0;
				$order_items = $this->_server->items[$merchant->id];
				print_r($order_items);
				foreach ($order->items as $item) {
					$product = $this->db->fetchOne('SELECT price, stock FROM products WHERE published = 1 AND price > 0 AND stock > 0 AND user_id = ? AND id = ?', Db::FETCH_OBJ, [$merchant->id, $item->product_id]);
					if (!$product) {
						throw new Error("Produk {$merchant->company} tidak valid! Silahkan cek pesanan Anda kembali atau hapus pesanan dari penjual tersebut untuk melanjutkan pemesanan!");
					}
					$original_bill += $product->price * min($item->quantity, $product->stock);
				}
				if ($coupon->minimum_purchase && $original_bill < $coupon->minimum_purchase) {
					throw new Error("Kode voucher {$order->coupon_code} berlaku jika belanja minimal Rp. " . number_format($coupon->minimum_purchase));
				}
				$discount = $coupon->discount_type == 1 ? $coupon->price_discount : ceil($coupon->price_discount * $original_bill / 100.0);
				if ($discount) {
					$discounts[] = ['merchant_id' => $merchant->id, 'discount' => $discount];
				}
			}
			$this->_response['status']            = 1;
			$this->_response['data']['discounts'] = $discounts;
		} catch (Error $e) {
			$this->_response['message'] = $e->getMessage();
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}