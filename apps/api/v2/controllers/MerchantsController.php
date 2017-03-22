<?php

namespace Application\Api\V2\Controllers;

use Application\Models\Post;
use Application\Models\ProductCategory;
use DateTime;
use Exception;
use IntlDateFormatter;
use Phalcon\Db;

class MerchantsController extends ControllerBase {
	function beforeExecuteRoute() {
		if ($this->dispatcher->getActionName() != 'about') {
			parent::beforeExecuteRoute();
		}
	}

	function indexAction() {
		$merchants = [];
		$query     = <<<QUERY
			SELECT
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
				COALESCE(c.minimum_purchase, a.minimum_purchase, d.value) AS minimum_purchase
			FROM
				users a
				JOIN roles b ON a.role_id = b.id
				JOIN service_areas c ON a.id = c.user_id
				JOIN settings d ON d.name = 'minimum_purchase'
			WHERE
QUERY;
		if ($this->_premium_merchant) {
			$query .= " a.id = {$this->_premium_merchant->id}";
		} else {
			$query .= <<<QUERY
				a.status = 1 AND
				a.premium_merchant IS NULL AND
				b.name = 'Merchant' AND
				c.village_id = {$this->_current_user->village->id}
QUERY;
		}
		$query .= <<<QUERY
			GROUP BY a.id
			ORDER BY a.company
QUERY;
		$result = $this->db->query($query);
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
			$merchant       = [
				'id'                    => $item->id,
				'company'               => $item->company,
				'address'               => $item->address,
				'business_days'         => trim(preg_replace(['/\,+/', '/([a-z])([A-Z])/', '/([A-Za-z]+)(-[A-Za-z]+)+(-[A-Za-z]+)/'], [',', '\1-\2', '\1\3'], implode('', $business_days)), ',') ?: '-',
				'business_opening_hour' => $item->business_opening_hour . '.00',
				'business_closing_hour' => $item->business_closing_hour . '.00 WIB',
				'delivery_hours'        => $delivery_hours ? $delivery_hours . ' WIB' : '-',
				'minimum_purchase'      => $item->minimum_purchase,
			];
			$merchants[] = $merchant;
		}
		if (!$merchants) {
			$this->_response['message'] = 'Maaf, daerah Anda di luar wilayah operasional Kami.';
		} else {
			$categories = ProductCategory::find([
				'conditions' => 'published = 1',
				'columns'    => 'id, name',
				'order'      => 'name',
			])->toArray();
			$this->_response['status'] = 1;
			$this->_response['data']   = [
				'merchants'  => $merchants,
				'categories' => $categories,
			];
		}
		$this->response->setJsonContent($this->_response);
		return $this->response;
	}

	function showAction($id) {
		try {
			if (!filter_var($id, FILTER_VALIDATE_INT)) {
				throw new Exception('Merchant tidak valid!');
			}
			$query = <<<QUERY
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
				WHERE
					a.status = 1 AND
					b.name = 'Merchant' AND
					c.village_id = {$this->_current_user->village->id} AND
QUERY;
			if ($this->_premium_merchant) {
				$query .= <<<QUERY
					a.premium_merchant = 1 AND
					a.id = {$this->_premium_merchant->id}
QUERY;
			} else {
				$query .= <<<QUERY
					a.premium_merchant IS NULL AND
					a.id = {$id}
QUERY;
			}
			$query .= ' ORDER BY a.company';
			$merchant = $this->db->fetchOne($query, Db::FETCH_OBJ);
			if (!$merchant) {
				throw new Exception('Merchant tidak valid!');
			}
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK);
			return $this->response;
		}
		$delivery_days  = [];
		$categories     = [];
		$now            = (new DateTime(null, $this->currentDatetime->getTimezone()))->setTimestamp($this->currentDatetime->getTimestamp());
		$date_formatter = new IntlDateFormatter(
			'id_ID',
			IntlDateFormatter::FULL,
			IntlDateFormatter::NONE,
			$this->currentDatetime->getTimezone(),
			IntlDateFormatter::GREGORIAN,
			'EEEE, d MMM yyyy'
		);
		if ($merchant->delivery_hours) {
			$merchant->delivery_hours = explode(',', $merchant->delivery_hours);
		}
		$delivery_hours = $merchant->delivery_hours ?: range($merchant->business_opening_hour, $merchant->business_closing_hour);
		for ($i = 0; $i < 7; $i++) {
			$now->modify("+{$i} day");
			$current_hour = $now->format('G');
			if (!$i && $current_hour >= max($delivery_hours)) {
				continue;
			}
			$current_day  = $now->format('l');
			$delivery_day = [
				'date'  => $now->format('Y-m-d'),
				'label' => $date_formatter->format($now),
			];
			if (($current_day == 'Sunday' && !$merchant->open_on_sunday) ||
				($current_day == 'Monday' && !$merchant->open_on_monday) ||
				($current_day == 'Tuesday' && !$merchant->open_on_tuesday) ||
				($current_day == 'Wednesday' && !$merchant->open_on_wednesday) ||
				($current_day == 'Thursday' && !$merchant->open_on_thursday) ||
				($current_day == 'Friday' && !$merchant->open_on_friday) ||
				($current_day == 'Saturday' && !$merchant->open_on_saturday)) {
				$delivery_day['unavailable'] = true;
			} else {
				$delivery_day['hours'] = !$i
					? array_values(array_filter($delivery_hours, function($v, $k) use($current_hour) {
						return $v > $current_hour;
					}, ARRAY_FILTER_USE_BOTH))
					: $delivery_hours;
			}
			$delivery_days[] = $delivery_day;
		}
		$result = $this->db->query(<<<QUERY
			SELECT
				c.id,
				c.name
			FROM
				store_items a
				JOIN products b ON a.product_id = b.id
				JOIN product_categories c ON b.product_category_id = c.id
			WHERE
				a.user_id = {$merchant->id} AND
				a.price > 0 AND
				a.published = 1 AND
				b.published = 1 AND
				c.published = 1
			GROUP BY c.id
			ORDER BY c.name
QUERY
		);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($item = $result->fetch()) {
			$categories[] = $item;
		}
		$this->_response['status']                = 1;
		$this->_response['data']['delivery_days'] = $delivery_days;
		$this->_response['data']['categories']    = $categories;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK);
		return $this->response;
	}

	function aboutAction() {
		$this->_response = [
			'status' => 1,
			'data'   => [
				'company_profile' => $this->_premium_merchant ? $this->_premium_merchant->company_profile : Post::findFirstByPermalink('tentang-kami')->body,
			],
		];
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK);
		return $this->response;
	}
}