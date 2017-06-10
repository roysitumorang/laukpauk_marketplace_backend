<?php

namespace Application\Api\V3\Controllers;

use DateInterval;
use DatePeriod;
use DateTime;
use Error;
use IntlDateFormatter;
use Phalcon\Db;
use stdClass;

class DeliverySchedulesController extends ControllerBase {
	function indexAction() {
		$merchant_ids   = $this->_server->merchant_ids;
		$delivery_dates = [];
		$current_hour   = $this->currentDatetime->format('G');
		$minimum_hour   = $current_hour + ($this->currentDatetime->format('i') > 29 ? 2 : 1);
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
		foreach (new DatePeriod($this->currentDatetime, new DateInterval('P1D'), 1) as $i => $date) {
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
				if ($day == $this->currentDatetime && $current_hour >= max($delivery_hours)) {
					continue;
				}
				$current_day  = $day->format('l');
				$current_date = $day->format('Y-m-d');
				if (!isset($delivery_dates[$current_date])) {
					$delivery_dates[$current_date]        = new stdClass;
					$delivery_dates[$current_date]->label = $label;
					$delivery_dates[$current_date]->hours = [];
				}
				$delivery_dates[$current_date]->hours = array_merge(
					$delivery_dates[$current_date]->hours,
					$day == $this->currentDatetime
					? array_values(array_filter($delivery_hours, function($v, $k) use($minimum_hour) {
						return $v >= $minimum_hour;
					}, ARRAY_FILTER_USE_BOTH))
					: $delivery_hours
				);
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

	function validateAction() {
		$merchant_ids  = $this->_server->merchant_ids;
		$delivery_date = $this->_server->delivery->date;
		$delivery_hour = filter_var($this->_server->delivery->hour, FILTER_VALIDATE_INT);
		$current_hour  = $this->currentDatetime->format('G');
		$minimum_hour  = $current_hour + ($this->currentDatetime->format('i') > 29 ? 2 : 1);
		$days_of_week  = [];
		$day_formatter = new IntlDateFormatter(
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
		try {
			$valid_date = false;
			foreach (new DatePeriod($this->currentDatetime, new DateInterval('P1D'), 1) as $i => $date) {
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
				if ($date->format('Y-m-d') === $delivery_date) {
					$valid_date = true;
				}
			}
			if (!$valid_date) {
				throw new Error('Tanggal tidak valid!');
			}
			if (!$delivery_hour) {
				throw new Error('Jam tidak valid!');
			}
			$delivery_schedule = new DateTime($delivery_date, $this->currentDatetime->getTimezone());
			$delivery_day      = $delivery_schedule->format('l');
			$query             = <<<QUERY
				SELECT
					a.id,
					a.company,
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
				if (($delivery_day == 'Sunday' && !$item->open_on_sunday) ||
					($delivery_day == 'Monday' && !$item->open_on_monday) ||
					($delivery_day == 'Tuesday' && !$item->open_on_tuesday) ||
					($delivery_day == 'Wednesday' && !$item->open_on_wednesday) ||
					($delivery_day == 'Thursday' && !$item->open_on_thursday) ||
					($delivery_day == 'Friday' && !$item->open_on_friday) ||
					($delivery_day == 'Saturday' && !$item->open_on_saturday)) {
					throw new Error("{$item->company} tutup pada tanggal tersebut, silahkan ganti tanggal atau hapus pesanan dari supplier tersebut!");
				}
				$delivery_hours = $item->delivery_hours
						? explode(',', $item->delivery_hours)
						: range($item->business_opening_hour, $item->business_closing_hour);
				if (($delivery_schedule->format('Y-m-d') === $this->currentDatetime->format('Y-m-d') && ($current_hour >= max($delivery_hours) || $delivery_hour < $minimum_hour))
					|| !in_array($delivery_hour, $delivery_hours)) {
					throw new Error("{$item->company} tidak melayani pengantaran pada jam tersebut, silahkan ganti jam atau hapus pesanan dari supplier tersebut!");
				}
			}
			$this->_response['status'] = 1;
		} catch (Error $e) {
			$this->_response['message'] = $e->getMessage();
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}