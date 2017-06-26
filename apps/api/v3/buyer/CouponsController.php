<?php

namespace Application\Api\V3\Buyer;

use Application\Models\Role;
use Phalcon\Db;
use Phalcon\Exception;

class CouponsController extends ControllerBase {
	function checkAction() {
		$code  = $this->_server->coupon_code;
		$total = 0;
		$today = $this->currentDatetime->format('Y-m-d');
		try {
			if (!$this->request->isOptions() || !$code) {
				throw new Exception('Request tidak valid!');
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
					LEFT JOIN orders b ON a.id = b.coupon_id AND b.status = 1 AND b.buyer_id = ?
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
				$code,
			];
			$params[0] .= ($this->_premium_merchant ? ' = 1' : ' IS NULL') . ' GROUP BY a.id';
			$coupon     = $this->db->fetchOne(array_shift($params), Db::FETCH_OBJ, $params);
			if (!$coupon || ($coupon->multiple_use && $coupon->usage > 1)) {
				throw new Exception("Kode voucher {$code} tidak valid! Silahkan cek lagi atau kosongkan untuk melanjutkan pemesanan.");
			}
			foreach ($this->_server->orders as $order) {
				$params = [<<<QUERY
					SELECT
						a.id,
						a.company,
						COALESCE(b.minimum_purchase, a.minimum_purchase, c.value::int) AS minimum_purchase
					FROM
						users a
						JOIN coverage_area b ON a.id = b.user_id
						JOIN settings c ON c.name = 'minimum_purchase'
					WHERE
						a.status = 1 AND
						a.role_id = ? AND
						a.id = ? AND
						b.village_id = ? AND
						a.premium_merchant
QUERY
					, Role::MERCHANT, $order->merchant_id, $this->_current_user->village_id];
				$params[0] .= ($this->_premium_merchant ? ' = 1' : ' IS NULL');
				$merchant   = $this->db->fetchOne(array_shift($params), Db::FETCH_OBJ, $params);
				if (!$merchant) {
					throw new Exception("Kode voucher {$code} tidak valid! Silahkan cek lagi atau kosongkan untuk melanjutkan pemesanan.");
				}
				foreach ($order->products as $item) {
					$product = $this->db->fetchOne('SELECT price, stock FROM user_product WHERE published = 1 AND price > 0 AND stock > 0 AND user_id = ? AND id = ?', Db::FETCH_OBJ, [$merchant->id, $item->id]);
					if (!$product) {
						throw new Exception("Produk {$merchant->company} tidak valid! Silahkan cek pesanan Anda kembali atau hapus pesanan dari penjual tersebut untuk melanjutkan pemesanan!");
					}
					$total += $product->price * min(max($item->quantity, 0), $product->stock);
				}
			}
			if ($coupon->minimum_purchase && $total < $coupon->minimum_purchase) {
				throw new Exception("Kode voucher {$code} berlaku untuk belanja minimal Rp. " . number_format($coupon->minimum_purchase));
			}
			$this->_response['status']           = 1;
			$this->_response['data']['discount'] = $coupon->discount_type == 1 ? $coupon->price_discount : ceil($coupon->price_discount * $total / 100.0);
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
	}
}