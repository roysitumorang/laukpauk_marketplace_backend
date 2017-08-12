<?php

namespace Application\Api\V3\Buyer;

use Application\Models\User;
use Application\Models\Role;
use Phalcon\Db;

class MerchantsController extends ControllerBase {
	function beforeExecuteRoute() {
		if (!in_array($this->dispatcher->getActionName(), ['about', 'termsConditions'])) {
			parent::beforeExecuteRoute();
		}
	}

	function indexAction() {
		$page         = $this->dispatcher->getParam('page', 'int');
		$search_query = $this->dispatcher->getParam('keyword', 'string') ?: null;
		$limit        = 10;
		$merchants    = [];
		$params       = [];
		$today        = $this->currentDatetime->format('N');
		$tomorrow     = $this->currentDatetime->modify('+1 day')->format('N');
		$stop_words   = preg_split('/,/', $this->db->fetchColumn("SELECT value FROM settings WHERE name = 'stop_words'"), -1, PREG_SPLIT_NO_EMPTY);
		$keywords     = '';
		if ($search_query) {
			$words = array_values(array_diff(preg_split('/ /', strtolower($search_query), -1, PREG_SPLIT_NO_EMPTY), $stop_words));
			foreach ($words as $i => $word) {
				$keywords .= ($i > 0 ? ' & ' : '') . $word . ':*';
			}
		}
		$query = <<<QUERY
			SELECT
				COUNT(DISTINCT a.id)
			FROM
				users a
				JOIN roles b ON a.role_id = b.id
				JOIN coverage_area c ON a.id = c.user_id
				JOIN user_product d ON a.id = d.user_id
				JOIN products e ON d.product_id = e.id
			WHERE
				a.status = 1 AND
				b.name = 'Merchant' AND
				c.village_id = {$this->_current_user->village->id} AND
				a.premium_merchant
QUERY;
		if ($this->_premium_merchant) {
			$query .= " = 1 AND a.id = {$this->_premium_merchant->id}";
			if ($search_query && $keywords) {
				$query .= " AND e.keywords @@ TO_TSQUERY('{$keywords}')";
			}
		} else {
			$query .= ' IS NULL';
			if ($search_query && $keywords) {
				$query .= ' AND (';
				foreach (['e.keywords', 'a.keywords'] as $i => $field) {
					$query .= ($i ? ' OR ' : '') . "{$field} @@ TO_TSQUERY('{$keywords}')";
				}
				$query .= ')';
			}
		}
		$total_merchants = $this->db->fetchColumn($query, $params);
		$total_pages     = ceil($total_merchants / $limit);
		$current_page    = $page > 0 ? $page : 1;
		$offset          = ($current_page - 1) * $limit;
		$result          = $this->db->query(strtr($query, ['COUNT(DISTINCT a.id)' => <<<QUERY
			a.id,
			a.company,
			a.address,
			a.open_on_sunday,
			a.open_on_monday,
			a.open_on_tuesday,
			a.open_on_wednesday,
			a.open_on_thursday,
			a.open_on_friday,
			a.open_on_saturday,
			a.business_opening_hour,
			a.business_closing_hour,
			a.delivery_hours,
			a.minimum_purchase,
			AVG(c.shipping_cost) AS shipping_cost,
			a.merchant_note,
			SUM(TS_RANK(e.keywords, TO_TSQUERY('{$keywords}')) + TS_RANK(e.keywords, TO_TSQUERY('{$keywords}'))) AS relevancy
QUERY
			]) . " GROUP BY a.id ORDER BY a.company LIMIT {$limit} OFFSET {$offset}", $params);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($item = $result->fetch()) {
			unset($row->relevancy);
			$availability = 'Hari ini ';
			if (($today == 1 && $item->open_on_monday) ||
				($today == 2 && $item->open_on_tuesday) ||
				($today == 3 && $item->open_on_wednesday) ||
				($today == 4 && $item->open_on_thursday) ||
				($today == 5 && $item->open_on_friday) ||
				($today == 6 && $item->open_on_saturday) ||
				($today == 7 && $item->open_on_sunday)) {
				$availability .= 'buka';
			} else {
				$availability .= 'tutup';
			}
			$availability .= ', besok ';
			if (($tomorrow == 1 && $item->open_on_monday) ||
				($tomorrow == 2 && $item->open_on_tuesday) ||
				($tomorrow == 3 && $item->open_on_wednesday) ||
				($tomorrow == 4 && $item->open_on_thursday) ||
				($tomorrow == 5 && $item->open_on_friday) ||
				($tomorrow == 6 && $item->open_on_saturday) ||
				($tomorrow == 7 && $item->open_on_sunday)) {
				$availability .= 'buka';
			} else {
				$availability .= 'tutup';
			}
			$business_days = [
				$item->open_on_monday    ? 'Senin'  : ',',
				$item->open_on_tuesday   ? 'Selasa' : ',',
				$item->open_on_wednesday ? 'Rabu'   : ',',
				$item->open_on_thursday  ? 'Kamis'  : ',',
				$item->open_on_friday    ? 'Jumat'  : ',',
				$item->open_on_saturday  ? 'Sabtu'  : ',',
				$item->open_on_sunday    ? 'Minggu' : ',',
			];
			$business_hours = range($item->business_opening_hour, $item->business_closing_hour);
			$hours          = explode(',', $item->delivery_hours);
			if ($hours) {
				foreach ($business_hours as &$hour) {
					if (!in_array($hour, $hours)) {
						$hour = ',';
					} else {
						$hour .= '.00';
					}
				}
			}
			$delivery_hours = trim(preg_replace(['/\,+/', '/(0)([1-9])/', '/([1-2]?[0-9]\.00)(-[1-2]?[0-9]\.00)+(-[1-2]?[0-9]\.00)/'], [',', '\1-\2', '\1\3'], implode('', $business_hours)), ',');
			$merchants[]    = [
				'id'               => $item->id,
				'company'          => $item->company,
				'address'          => $item->address,
				'availability'     => $availability,
				'business_days'    => trim(preg_replace(['/\,+/', '/([a-z])([A-Z])/', '/([A-Za-z]+)(-[A-Za-z]+)+(-[A-Za-z]+)/'], [',', '\1-\2', '\1\3'], implode('', $business_days)), ',') ?: '-',
				'business_hours'   => $item->business_opening_hour . '.00 - ' . $item->business_closing_hour . '.00 WIB',
				'delivery_hours'   => $delivery_hours ? $delivery_hours . ' WIB' : '-',
				'minimum_purchase' => $item->minimum_purchase,
				'shipping_cost'    => $item->shipping_cost,
				'merchant_note'    => $item->merchant_note,
			];
		}
		if (!$merchants) {
			if ($search_query) {
				$this->_response['message'] = 'Penjual tidak ditemukan.';
			} else if (!$total_pages) {
				$this->_response['message'] = 'Maaf, daerah Anda di luar wilayah operasional Kami.';
			}
		} else {
			$this->_response['status']            = 1;
			$this->_response['data']['merchants'] = $merchants;
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function aboutAction() {
		if ($merchant_token = $this->dispatcher->getParam('merchant_token', 'string')) {
			$premium_merchant = User::findFirst(['status = 1 AND premium_merchant = 1 AND role_id = ?0 AND merchant_token = ?1', 'bind' => [Role::MERCHANT, $merchant_token]]);

		}
		if (!$premium_merchant) {
			$premium_merchant = User::findFirst(1);
		}
		$this->_response = [
			'status' => 1,
			'data'   => ['company_profile' => $premium_merchant->company_profile],
		];
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function termsConditionsAction() {
		if ($merchant_token = $this->dispatcher->getParam('merchant_token', 'string')) {
			$premium_merchant = User::findFirst(['status = 1 AND premium_merchant = 1 AND role_id = ?0 AND merchant_token = ?1', 'bind' => [Role::MERCHANT, $merchant_token]]);

		}
		if (!$premium_merchant) {
			$premium_merchant = User::findFirst(1);
		}
		$this->_response = [
			'status' => 1,
			'data'   => ['terms_conditions' => $premium_merchant->terms_conditions],
		];
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}