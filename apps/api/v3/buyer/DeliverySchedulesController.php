<?php

namespace Application\Api\V3\Buyer;

use DateInterval;
use DatePeriod;
use DateTime;
use IntlDateFormatter;
use Phalcon\Db\Enum;

class DeliverySchedulesController extends ControllerBase {
	private $_day_formatter;

	function initialize() {
		parent::initialize();
		$this->_day_formatter = new IntlDateFormatter(
			'id_ID',
			IntlDateFormatter::FULL,
			IntlDateFormatter::NONE,
			$this->currentDatetime->getTimezone(),
			IntlDateFormatter::GREGORIAN,
			'EEEE'
		);
	}

	function indexAction() {
		try {
			$merchant_ids = array_filter(is_array($this->_server->merchant_ids) ? $this->_server->merchant_ids : []);
			$query        = sprintf(<<<QUERY
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
					a.id IN(%s)
QUERY
			, implode(',', $merchant_ids));
			if (!$merchant_ids || count($merchant_ids) != $this->db->fetchColumn($query)) {
				throw new \Exception('Maaf, Supplier tidak ditemukan.');
			}
			$delivery_dates     = [];
			$delivery_hours     = [];
			$all_delivery_dates = [];
			$current_hour       = $this->currentDatetime->format('G');
			$minimum_hour       = $current_hour + ($current_hour < 16 && $this->currentDatetime->format('i') > 29 ? 2 : 1);
			$today              = lcfirst($this->currentDatetime->format('l'));
			$tomorrow           = lcfirst($this->currentDatetime->modify('+1 day')->format('l'));
			$available_days     = [];
			foreach (new DatePeriod($this->currentDatetime, new DateInterval('P1D'), 1) as $i => $date) {
				$day                  = ($i ? 'Besok' : 'Hari Ini') . ' / ' . $this->_date_formatter->format($date);
				$available_days[$day] = $date;
				$delivery_hours[$day] = [];
			}
			$result = $this->db->query(strtr($query, ['COUNT(DISTINCT a.id)' => "a.id, a.open_on_{$today} AS open_today, a.open_on_{$tomorrow} AS open_tomorrow, a.business_opening_hour, a.business_closing_hour, a.delivery_hours"]) . ' GROUP BY a.id');
			$result->setFetchMode(Enum::FETCH_OBJ);
			while ($merchant = $result->fetch()) {
				$merchant_delivery_dates = [];
				$merchant_delivery_hours = $merchant->delivery_hours
							? explode(',', $merchant->delivery_hours)
							: range($merchant->business_opening_hour, $merchant->business_closing_hour);
				foreach ($available_days as $label => $day) {
					if (($day == $this->currentDatetime && (!$merchant->open_today || $current_hour >= max($merchant_delivery_hours))) ||
						($day > $this->currentDatetime && !$merchant->open_tomorrow)) {
						$delivery_hours[$label][] = [];
						continue;
					}
					$merchant_delivery_dates[] = $label;
					$delivery_hours[$label][]  = $day == $this->currentDatetime
								? array_values(array_filter($merchant_delivery_hours, function($v, $k) use($minimum_hour) {
									return $v >= $minimum_hour;
								}, ARRAY_FILTER_USE_BOTH))
								: $merchant_delivery_hours;
				}
				$all_delivery_dates[] = $merchant_delivery_dates;
			}
			if (!$all_delivery_dates) {
				$filtered_delivery_dates = [];
			} else if (count($all_delivery_dates) === 1) {
				$filtered_delivery_dates = $all_delivery_dates[0];
			} else {
				$filtered_delivery_dates = array_values(call_user_func_array('array_intersect', $all_delivery_dates));
			}
			if (!$filtered_delivery_dates) {
				throw new \Exception('Maaf, Supplier tutup.');
			} else {
				foreach ($available_days as $label => $day) {
					if (!in_array($label, $filtered_delivery_dates)) {
						continue;
					}
					$current_delivery_hours                = $delivery_hours[$label];
					$delivery_dates[$day->format('Y-m-d')] = [
						'label' => $label,
						'hours' => count($current_delivery_hours) > 1 ? array_values(call_user_func_array('array_intersect', $current_delivery_hours)) : $current_delivery_hours[0],
					];
				}
				$this->_response['status']                 = 1;
				$this->_response['data']['delivery_dates'] = $delivery_dates;
			}
		} catch (\Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
	}

	function checkAction() {
		try {
			$merchant_ids = array_filter(is_array($this->_server->merchant_ids) ? $this->_server->merchant_ids : []);
			$query        = sprintf(<<<QUERY
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
					a.id IN(%s)
QUERY
			, implode(',', $merchant_ids));
			if (!$merchant_ids || count($merchant_ids) != $this->db->fetchColumn($query)) {
				throw new \Exception('Maaf, Supplier tidak ditemukan.');
			}
			$delivery_date = $this->_server->delivery->date;
			$delivery_hour = filter_var($this->_server->delivery->hour, FILTER_VALIDATE_INT);
			try {
				$delivery_schedule = new DateTime($delivery_date, $this->currentDatetime->getTimezone());
				if ($delivery_schedule < $this->currentDatetime->setTime(0, 0) || $delivery_schedule > $this->currentDatetime->modify('+1 day')->setTime(23, 59, 59)) {
					throw new \Exception;
				}
			} catch (\Exception $e) {
				throw new \Exception('Tanggal tidak valid!');
			}
			if (!$delivery_hour || $delivery_hour < 0 || $delivery_hour > 23) {
				throw new \Exception('Jam tidak valid!');
			}
			$current_hour = $this->currentDatetime->format('G');
			$minimum_hour = $current_hour + ($current_hour < 16 && $this->currentDatetime->format('i') > 29 ? 2 : 1);
			$today        = lcfirst($this->currentDatetime->format('l'));
			$tomorrow     = lcfirst($this->currentDatetime->modify('+1 day')->format('l'));
			$result       = $this->db->query(strtr($query, ['COUNT(DISTINCT a.id)' => "a.id, a.company, a.open_on_{$today} AS open_today, a.open_on_{$tomorrow} AS open_tomorrow, a.business_opening_hour, a.business_closing_hour, a.delivery_hours"]) . ' GROUP BY a.id');

			$result->setFetchMode(Enum::FETCH_OBJ);
			while ($item = $result->fetch()) {
				if (($delivery_schedule->format('Y-m-d') === $this->currentDatetime->format('Y-m-d') && !$item->open_today) ||
					($delivery_schedule > $this->currentDatetime && !$item->open_tomorrow)) {
					throw new \Exception("{$item->company} tutup pada tanggal tersebut, silahkan ganti tanggal atau hapus pesanan dari supplier tersebut!");
				}
				$delivery_hours = $item->delivery_hours
						? explode(',', $item->delivery_hours)
						: range($item->business_opening_hour, $item->business_closing_hour);
				if (($delivery_schedule->format('Y-m-d') === $this->currentDatetime->format('Y-m-d') && ($current_hour >= max($delivery_hours) || $delivery_hour < $minimum_hour))
					|| !in_array($delivery_hour, $delivery_hours)) {
					throw new \Exception("{$item->company} tidak melayani pengantaran pada jam tersebut, silahkan ganti jam atau hapus pesanan dari supplier tersebut!");
				}
			}
			$this->_response['status'] = 1;
		} catch (\Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
	}
}