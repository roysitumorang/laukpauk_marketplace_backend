<?php

namespace Application\Api\V3\Buyer;

use Application\Models\Release;
use Application\Models\Role;
use Exception;
use Phalcon\Db;

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
					a.code = ?
				GROUP BY a.id, d.version
QUERY
				,
				$this->_current_user->id,
				$today,
				$today,
				$code,
			];
			$coupon = $this->db->fetchOne(array_shift($params), Db::FETCH_OBJ, $params);
			if (!$coupon ||
				($coupon->maximum_usage && $coupon->total_usage >= $coupon->maximum_usage) ||
				($coupon->minimum_version && (!$this->_server->app_version || !Release::findFirst(['user_type = ?0 AND version = ?1', 'bind' => ['buyer', $this->_server->app_version]]) || $this->_server->app_version < $coupon->minimum_version)) ||
				(!$coupon->multiple_use && $coupon->personal_usage >= 1)) {
				throw new Exception("Kode voucher {$code} tidak valid! Silahkan cek lagi atau kosongkan untuk melanjutkan pemesanan.");
			}
			foreach ($this->_server->orders as $cart) {
				$params = [<<<QUERY
					SELECT
						a.id,
						a.company,
						b.shipping_cost,
						a.minimum_purchase
					FROM
						users a
						JOIN coverage_area b ON a.id = b.user_id
					WHERE
						a.status = 1 AND
						a.role_id = ? AND
						a.id = ? AND
						b.village_id = ?
QUERY
					, Role::MERCHANT, $cart->merchant_id, $this->_current_user->village->id];
				$merchant = $this->db->fetchOne(array_shift($params), Db::FETCH_OBJ, $params);
				if (!$merchant) {
					throw new Exception("Kode voucher {$code} tidak valid! Silahkan cek lagi atau kosongkan untuk melanjutkan pemesanan.");
				}
				$purchase = 0;
				foreach ($cart->products as $item) {
					$product = $this->db->fetchOne('SELECT price, stock FROM user_product WHERE published = 1 AND price > 0 AND stock > 0 AND user_id = ? AND id = ?', Db::FETCH_OBJ, [$merchant->id, $item->id]);
					if (!$product) {
						throw new Exception("Produk {$merchant->company} tidak valid! Silahkan cek pesanan Anda kembali atau hapus pesanan dari penjual tersebut untuk melanjutkan pemesanan!");
					}
					$purchase += min(max($item->quantity, 0), $product->stock) * $product->price;
				}
				foreach ($cart->sale_packages as $item) {
					$sale_package = $this->db->fetchOne("SELECT id, name, price, stock FROM sale_packages WHERE published = '1' AND price > 0 AND stock > 0 AND user_id = ? AND id = ?", Db::FETCH_OBJ, [$merchant->id, $item->id]);
					if (!$sale_package) {
						throw new Exception('Order Anda tidak valid');
					}
					$purchase += min(max($item->quantity, 0), $sale_package->stock) * $sale_package->price;
				}
				if ($purchase < $merchant->minimum_purchase) {
					throw new Exception("Order di {$merchant->company} minimal Rp. " . number_format($merchant->minimum_purchase) . " untuk dapat diproses!");
				}
				$total += $purchase;
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