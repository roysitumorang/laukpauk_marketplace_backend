<?php

namespace Application\Api\V3\Controllers;

use Phalcon\Db;

class CouponsController extends ControllerBase {
	function indexAction() {
		$coupons      = [];
		$current_date = $this->currentDatetime->format('Y-m-d');
		$query        = <<<QUERY
			SELECT
				a.id,
				a.code,
				a.discount_amount,
				a.discount_type,
				a.multiple_use,
				a.minimum_purchase,
				COUNT(b.id) AS usages
			FROM
				coupons a
				LEFT JOIN orders b ON a.id = b.coupon_id AND b.buyer_id = {$this->_current_user->id} AND b.status = '1'
			WHERE
				a.status = '1' AND
				a.effective_date <= '{$current_date}' AND
				a.expiry_date > '{$current_date}' AND
				a.user_id
QUERY;
		if ($this->_premium_merchant) {
			$query .= " = {$this->_premium_merchant->id}";
		} else {
			$query .= ' IS NULL';
		}
		$query .= ' GROUP BY a.id';
		$result = $this->db->query($query);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($item = $result->fetch()) {
			if ($item->multiple_use == 1 && $item->usage > 1) {
				continue;
			}
			unset($item->multiple_use, $item->usages);
			$coupons[$item->code] = $item;
		}
		$this->_response['status']          = 1;
		$this->_response['data']['coupons'] = $coupons;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}