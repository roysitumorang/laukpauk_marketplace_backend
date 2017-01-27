<?php

namespace Application\Api\V1\Controllers;

use Application\Models\ProductCategory;
use DateTime;
use Exception;
use IntlDateFormatter;
use Phalcon\Db;

class MerchantsController extends ControllerBase {
	function indexAction() {
		$merchants = [];
		$result    = $this->db->query(<<<QUERY
			SELECT
				a.id,
				a.company
			FROM
				users a
				JOIN roles b ON a.role_id = b.id
				JOIN service_areas c ON a.id = c.user_id
			WHERE
				a.status = 1 AND
				b.name = 'Merchant' AND
				c.village_id = {$this->_current_user->village->id}
			ORDER BY a.company
QUERY
		);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($merchant = $result->fetch()) {
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
			$this->_response = [
				'status' => 1,
				'data'   => [
					'merchants'  => $merchants,
					'categories' => $categories,
				],
			];
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK);
		return $this->response;
	}

	function showAction($id) {
		try {
			if (!filter_var($id, FILTER_VALIDATE_INT)) {
				throw new Exception('Merchant tidak valid!');
			}
			$merchant = $this->db->fetchOne(<<<QUERY
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
					a.business_closing_hour
				FROM
					users a
					JOIN roles b ON a.role_id = b.id
					JOIN service_areas c ON a.id = c.user_id
				WHERE
					a.status = 1 AND
					b.name = 'Merchant' AND
					c.village_id = {$this->_current_user->village->id} AND
					a.id = {$id}
				ORDER BY a.company
QUERY
			, Db::FETCH_OBJ);
			if (!$merchant) {
				throw new Exception('Merchant tidak valid!');
			}
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK);
			return $this->response;
		}
		$delivery_days  = [];
		$now            = (new DateTime(null, $this->currentDatetime->getTimezone()))->setTimestamp($this->currentDatetime->getTimestamp());
		$date_formatter = new IntlDateFormatter(
			'id_ID',
			IntlDateFormatter::FULL,
			IntlDateFormatter::NONE,
			$this->config->timezone,
			IntlDateFormatter::GREGORIAN,
			'EEEE, d MMM yyyy'
		);
		$business_hours = range($merchant->business_opening_hour, $merchant->business_closing_hour);
		for ($i = 0; $i < 7; $i++) {
			$current_hour = $now->format('G');
			if (!$i && $current_hour >= $merchant->business_closing_hour) {
				continue;
			}
			if ($i) {
				$now->modify('+1 day');
			}
			$current_day   = $now->format('l');
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
				$delivery_day['hours'] = !$i ? range($current_hour, $merchant->business_closing_hour) : $business_hours;
			}
			$delivery_days[] = $delivery_day;
		}
		$this->_response = [
			'status' => 1,
			'data'   => ['delivery_days' => $delivery_days],
		];
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK);
		return $this->response;
	}
}