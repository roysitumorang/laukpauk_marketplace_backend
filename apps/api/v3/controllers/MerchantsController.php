<?php

namespace Application\Api\V3\Controllers;

use Application\Models\Post;
use Application\Models\User;
use Application\Models\Role;
use DatePeriod;
use DateInterval;
use IntlDateFormatter;
use Phalcon\Db;
use stdClass;

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
		$query        = <<<QUERY
			SELECT
				COUNT(DISTINCT a.id)
			FROM
				users a
				JOIN roles b ON a.role_id = b.id
				JOIN service_areas c ON a.id = c.user_id
				JOIN settings d ON d.name = 'minimum_purchase'
				JOIN products e ON a.id = e.user_id
				JOIN product_categories f ON e.product_category_id = f.id
			WHERE
				a.status = 1 AND
				b.name = 'Merchant' AND
				c.village_id = {$this->_current_user->village->id} AND
QUERY;
		if ($this->_premium_merchant) {
			$query .= " a.premium_merchant = 1 AND a.id = {$this->_premium_merchant->id}";
		} else {
			$query .= ' a.premium_merchant IS NULL';
			if ($search_query) {
				$stop_words            = preg_split('/,/', $this->db->fetchColumn("SELECT value FROM settings WHERE name = 'stop_words'"), -1, PREG_SPLIT_NO_EMPTY);
				$keywords              = preg_split('/ /', strtolower($search_query), -1, PREG_SPLIT_NO_EMPTY);
				$filtered_keywords     = array_diff($keywords, $stop_words);
				$filtered_search_query = implode(' ', $filtered_keywords);
				$query                .= ' AND (a.company ILIKE ? OR e.name ILIKE ? OR f.name ILIKE ?';
				foreach (range(1, 3) as $i) {
					$params[] = "%{$filtered_search_query}%";
				}
				if (count($filtered_keywords) > 1) {
					foreach ($filtered_keywords as $keyword) {
						$query .= ' OR a.company ILIKE ? OR e.name ILIKE ? OR f.name ILIKE ?';
						foreach (range(1, 3) as $i) {
							$params[] = "%{$keyword}%";
						}
					}
				}
				$query .= ')';
			}
		}
		$total_merchants = $this->db->fetchColumn($query, $params);
		$total_pages     = ceil($total_merchants / $limit);
		$current_page    = $page > 0 ? $page : 1;
		$offset          = ($current_page - 1) * $limit;
		$result          = $this->db->query(str_replace('COUNT(DISTINCT a.id)', 'DISTINCT a.id, a.company, a.address, a.open_on_sunday, a.open_on_monday, a.open_on_tuesday, a.open_on_wednesday, a.open_on_thursday, a.open_on_friday, a.open_on_saturday, a.business_opening_hour, a.business_closing_hour, a.delivery_hours, COALESCE(c.minimum_purchase, a.minimum_purchase, d.value::INT) AS minimum_purchase, a.shipping_cost', $query) . " ORDER BY a.company LIMIT {$limit} OFFSET {$offset}", $params);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($item = $result->fetch()) {
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
			if ($hours = explode(',', $item->delivery_hours)) {
				foreach ($business_hours as &$hour) {
					if (!in_array($hour, $hours)) {
						$hour = ',';
					} else {
						$hour .= '.00';
					}
				}
			}
			$delivery_hours = trim(preg_replace(['/\,+/', '/(0)([1-9])/', '/([1-2]?[0-9]\.00)(-[1-2]?[0-9]\.00)+(-[1-2]?[0-9]\.00)/'], [',', '\1-\2', '\1\3'], implode('', $business_hours)), ',');
			$merchants[] = [
				'id'               => $item->id,
				'company'          => $item->company,
				'address'          => $item->address,
				'business_days'    => trim(preg_replace(['/\,+/', '/([a-z])([A-Z])/', '/([A-Za-z]+)(-[A-Za-z]+)+(-[A-Za-z]+)/'], [',', '\1-\2', '\1\3'], implode('', $business_days)), ',') ?: '-',
				'business_hours'   => $item->business_opening_hour . '.00 - ' . $item->business_closing_hour . '.00 WIB',
				'delivery_hours'   => $delivery_hours ? $delivery_hours . ' WIB' : '-',
				'minimum_purchase' => $item->minimum_purchase,
				'shipping_cost'    => $item->shipping_cost ?? 0,
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
		$this->_response = [
			'status' => 1,
			'data'   => [
				'company_profile' => $premium_merchant ? $premium_merchant->company_profile : Post::findFirstByPermalink('tentang-kami')->body,
			],
		];
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK);
		return $this->response;
	}

	function termsConditionsAction() {
		if ($merchant_token = $this->dispatcher->getParam('merchant_token', 'string')) {
			$premium_merchant = User::findFirst(['status = 1 AND premium_merchant = 1 AND role_id = ?0 AND merchant_token = ?1', 'bind' => [Role::MERCHANT, $merchant_token]]);

		}
		$this->_response = [
			'status' => 1,
			'data'   => [
				'terms_conditions' => $premium_merchant ? $premium_merchant->terms_conditions : Post::findFirstByPermalink('syarat-ketentuan')->body,
			],
		];
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK);
		return $this->response;
	}

	function deliverySchedulesAction() {
		$merchant_ids   = explode('-', $this->dispatcher->getParam('merchant_ids'));
		$delivery_dates = [];
		$current_hour   = $this->currentDatetime->format('G');
		$days_of_week   = [];
		$day_formatter  = new IntlDateFormatter(
			'id_ID',
			IntlDateFormatter::FULL,
			IntlDateFormatter::NONE,
			$this->currentDatetime->getTimezone(),
			IntlDateFormatter::GREGORIAN,
			'EEEE'
		);
		$date_formatter = new IntlDateFormatter(
			'id_ID',
			IntlDateFormatter::FULL,
			IntlDateFormatter::NONE,
			$this->currentDatetime->getTimezone(),
			IntlDateFormatter::GREGORIAN,
			'd MMM yyyy'
		);
		foreach (new DatePeriod($this->currentDatetime, new DateInterval('P1D'), 6) as $i => $date) {
			$date = clone $this->currentDatetime->modify("+{$i} day");
			if (!$i) {
				$day = 'Hari Ini';
			} else if ($i === 1) {
				$day = 'Besok';
			} else if ($i === 2) {
				$day = 'Lusa';
			} else {
				$day = $day_formatter->format($date) . ' Depan';
			}
			$day               .= ' / ' . $date_formatter->format($date);
			$days_of_week[$day] = $date;
		}
		$query = <<<QUERY
			SELECT
				a.id,
				a.open_on_sunday,
				a.open_on_monday,
				a.open_on_tuesday,
				a.open_on_wednesday,
				a.open_on_thursday,
				a.open_on_friday,
				a.open_on_saturday,
				a.business_opening_hour,
				a.business_closing_hour,
				a.delivery_hours
			FROM
				users a
				JOIN roles b ON a.role_id = b.id
				JOIN service_areas c ON a.id = c.user_id
				JOIN products d ON a.id = d.user_id
			WHERE
				a.status = 1 AND
				b.name = 'Merchant' AND
				c.village_id = {$this->_current_user->village->id} AND
QUERY;
		if ($this->_premium_merchant) {
			$query .= " a.premium_merchant = 1 AND a.id = {$this->_premium_merchant->id}";
		} else {
			$query .= ' a.premium_merchant IS NULL AND a.id IN(' . implode(',', $merchant_ids) . ')';
		}
		$query .= ' GROUP BY a.id';
		$result = $this->db->query($query);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($item = $result->fetch()) {
			$delivery_hours = $item->delivery_hours
					? explode(',', $item->delivery_hours)
					: range($item->business_opening_hour, $item->business_closing_hour);
			foreach ($days_of_week as $label => $day) {
				if ($day === $this->currentDatetime && $current_hour >= max($delivery_hours)) {
					continue;
				}
				$current_day  = $day->format('l');
				$current_date = $day->format('Y-m-d');
				if (!isset($delivery_dates[$current_date])) {
					$delivery_dates[$current_date]        = new stdClass;
					$delivery_dates[$current_date]->label = $label;
					$delivery_dates[$current_date]->hours = [];
				}
				if (($current_day == 'Sunday' && $item->open_on_sunday) ||
					($current_day == 'Monday' && $item->open_on_monday) ||
					($current_day == 'Tuesday' && $item->open_on_tuesday) ||
					($current_day == 'Wednesday' && $item->open_on_wednesday) ||
					($current_day == 'Thursday' && $item->open_on_thursday) ||
					($current_day == 'Friday' && $item->open_on_friday) ||
					($current_day == 'Saturday' && $item->open_on_saturday)) {
					$minimum_hour                         = $current_hour + ($day->format('i') > 29 ? 2 : 1);
					$delivery_dates[$current_date]->hours = array_merge(
						$delivery_dates[$current_date]->hours,
						$day === $this->currentDatetime
						? array_filter($delivery_hours, function($k) use($minimum_hour) {
							return $k >= $minimum_hour;
						}, ARRAY_FILTER_USE_KEY)
						: $delivery_hours);
				}
			}
		}
		if (!$delivery_dates) {
			$this->_response['message'] = 'Maaf, Supplier tidak ditemukan.';
		} else {
			array_walk($delivery_dates, function(&$item, $key) {
				$item->hours = array_values(array_unique($item->hours));
			});
			$this->_response['status']                 = 1;
			$this->_response['data']['delivery_dates'] = array_filter($delivery_dates, function($item, $key) {
										return !empty($item->hours);
									}, ARRAY_FILTER_USE_BOTH);
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function reviewDeliveryScheduleAction() {
	}
}