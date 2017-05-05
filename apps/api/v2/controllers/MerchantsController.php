<?php

namespace Application\Api\V2\Controllers;

use Application\Models\Post;
use Application\Models\ProductCategory;
use Application\Models\User;
use Application\Models\Role;
use DateTime;
use Exception;
use IntlDateFormatter;
use Phalcon\Db;

class MerchantsController extends ControllerBase {
	function beforeExecuteRoute() {
		if (!in_array($this->dispatcher->getActionName(), ['about', 'termsConditions'])) {
			parent::beforeExecuteRoute();
		}
	}

	function indexAction() {
		$page      = $this->dispatcher->getParam('page', 'int');
		$keyword   = $this->dispatcher->getParam('keyword', 'string');
		$limit     = 10;
		$merchants = [];
		$params    = [];
		$query     = <<<QUERY
			SELECT
				COUNT(1)
			FROM
				users a
				JOIN roles b ON a.role_id = b.id
				JOIN service_areas c ON a.id = c.user_id
				JOIN settings d ON d.name = 'minimum_purchase'
			WHERE
				a.status = 1 AND
				b.name = 'Merchant' AND
				c.village_id = {$this->_current_user->village->id} AND
QUERY;
		if ($this->_premium_merchant) {
			$query .= " a.premium_merchant = 1 AND a.id = {$this->_premium_merchant->id}";
		} else {
			$query .= ' a.premium_merchant IS NULL';
			if ($keyword) {
				$params[] = "%{$keyword}%";
				$query   .= ' AND a.company LIKE ?';
			}
		}
		$total_merchants = $this->db->fetchColumn($query, $params);
		$total_pages     = ceil($total_merchants / $limit);
		$current_page    = $page > 0 && $page <= $total_pages ? $page : 1;
		$offset          = ($current_page - 1) * $limit;
		$result          = $this->db->query(str_replace('COUNT(1)', 'a.id, a.company, a.address, a.open_on_sunday, a.open_on_monday, a.open_on_tuesday, a.open_on_wednesday, a.open_on_thursday, a.open_on_friday, a.open_on_saturday, a.business_opening_hour, a.business_closing_hour, a.delivery_hours, COALESCE(c.minimum_purchase, a.minimum_purchase, d.value) AS minimum_purchase, a.shipping_cost', $query) . " GROUP BY a.id ORDER BY a.company LIMIT {$limit} OFFSET {$offset}", $params);
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
				'shipping_cost'         => $item->shipping_cost ?? 0,
			];
			$merchants[] = $merchant;
		}
		if (!$merchants) {
			$this->_response['message'] = $keyword ? 'Penjual tidak ditemukan.' : 'Maaf, daerah Anda di luar wilayah operasional Kami.';
		} else {
			$categories = ProductCategory::find([
				'conditions' => 'published = 1',
				'columns'    => 'id, name',
				'order'      => 'name',
			])->toArray();
			$this->_response['status'] = 1;
			$this->_response['data']   = [
				'merchants'    => $merchants,
				'categories'   => $categories,
				'current_page' => $current_page,
				'pages'        => $this->_setPaginationRange($total_pages, $current_page),
			];
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
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
				$minimum_hour          = $current_hour + ($now->format('i') > 29 ? 2 : 1);
				$delivery_day['hours'] = !$i
					? array_values(array_filter($delivery_hours, function($v, $k) use($minimum_hour) {
						return $v >= $minimum_hour;
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
}