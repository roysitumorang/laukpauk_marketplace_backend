<?php

namespace Application\Api\V3\Buyer;

use Application\Models\Post;
use Phalcon\Db;

class MerchantsController extends ControllerBase {
	function beforeExecuteRoute() {
		if (!in_array($this->dispatcher->getActionName(), ['about', 'termsConditions'])) {
			parent::beforeExecuteRoute();
		}
	}

	function indexAction() {
		$current_page = $this->dispatcher->getParam('page', 'int!', 1);
		$search_query = $this->dispatcher->getParam('keyword', 'string') ?: null;
		$limit        = 10;
		$merchants    = [];
		$params       = [];
		$today        = lcfirst($this->currentDatetime->format('l'));
		$tomorrow     = lcfirst($this->currentDatetime->modify('+1 day')->format('l'));
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
				c.village_id = {$this->_current_user->village->id}
QUERY;
		if ($search_query && $keywords) {
			$query .= ' AND (';
			foreach (['e.keywords', 'a.keywords'] as $i => $field) {
				$query .= ($i ? ' OR ' : '') . "{$field} @@ TO_TSQUERY('{$keywords}')";
			}
			$query .= ')';
		}
		$total_merchants = $this->db->fetchColumn($query, $params);
		$total_pages     = ceil($total_merchants / $limit);
		$offset          = ($current_page - 1) * $limit;
		$result          = $this->db->query(strtr($query, ['COUNT(DISTINCT a.id)' => <<<QUERY
			a.id,
			a.company,
			a.address,
			a.open_on_{$today} AS open_today,
			a.open_on_{$tomorrow} AS open_tomorrow,
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
			unset($item->relevancy);
			$availability = 'Hari ini ';
			if ($item->open_today && $item->business_closing_hour > $this->currentDatetime->format('G')) {
				$availability .= 'buka';
			} else {
				$availability .= 'tutup';
			}
			$availability .= ', besok ';
			if ($item->open_tomorrow) {
				$availability .= 'buka';
			} else {
				$availability .= 'tutup';
			}
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
		$this->_response = [
			'status' => 1,
			'data'   => ['company_profile' => Post::findFirstBySubject('Tentang Kami')->body],
		];
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function termsConditionsAction() {
		$this->_response = [
			'status' => 1,
			'data'   => ['terms_conditions' => Post::findFirstBySubject('Kebijakan dan Privasi')->body],
		];
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}