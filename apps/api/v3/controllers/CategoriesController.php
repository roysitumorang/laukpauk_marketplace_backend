<?php

namespace Application\Api\V3\Controllers;

use DateTime;
use IntlDateFormatter;
use Phalcon\Db;

class CategoriesController extends ControllerBase {
	function indexAction() {
		$categories       = [];
		$merchant_ids     = [];
		$merchants        = [];
		$picture_root_url = 'http' . ($this->request->getScheme() === 'https' ? 's' : '') . '://' . $this->request->getHttpHost() . '/assets/image/';
		$day_aliases      = ['Hari ini', 'Besok', 'Lusa', '3 hari lagi', '4 hari lagi', '5 hari lagi', '6 hari lagi'];
		$date_formatter   = new IntlDateFormatter(
			'id_ID',
			IntlDateFormatter::FULL,
			IntlDateFormatter::NONE,
			$this->currentDatetime->getTimezone(),
			IntlDateFormatter::GREGORIAN,
			'EEEE, d MMM yyyy'
		);
		$result = $this->db->query('SELECT id, name FROM product_categories WHERE user_id ' . ($this->_premium_merchant ? "= {$this->_premium_merchant->id}" : 'IS NULL') . ' AND published = 1 ORDER BY name');
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($category = $result->fetch()) {
			$category->products = [];
			$query              = <<<QUERY
				SELECT
					d.id,
					d.user_id,
					d.product_category_id,
					d.name,
					d.price,
					d.stock,
					d.stock_unit,
					d.picture,
					COALESCE(SUM(g.quantity), 0) AS total_sale
				FROM
					users a
					JOIN roles b ON a.role_id = b.id
					JOIN service_areas c ON a.id = c.user_id
					JOIN products d ON a.id = d.id
					JOIN product_categories f ON d.product_category_id = f.id
					LEFT JOIN order_items g ON d.id = g.product_id
					LEFT JOIN orders h ON g.order_id = h.id AND h.status = 1
				WHERE
					a.status = 1 AND
					b.name = 'Merchant' AND
					c.village_id = {$this->_current_user->village->id} AND
					d.published = 1 AND
					d.stock > 0 AND
					f.published = 1 AND
					a.premium_merchant
QUERY;
			if ($this->_premium_merchant) {
				$query .= " = 1 AND a.id = {$this->_premium_merchant->id}";
			} else {
				$query .= ' IS NULL';
			}
			$query .= " AND f.id = {$category->id} GROUP BY d.id ORDER BY total_sale DESC LIMIT 2 OFFSET 0";
			$sub_result = $this->db->query($query);
			$sub_result->setFetchMode(Db::FETCH_OBJ);
			while ($product = $sub_result->fetch()) {
				in_array($product->user_id, $merchant_ids) || $merchant_ids[] = $product->user_id;
				if ($product->picture) {
					$product->picture = $picture_root_url . strtr($product->picture, ['.jpg' => '120.jpg']);
				} else {
					unset($product->picture);
				}
				unset($product->total_sale);
				$category->products[] = $product;
			}
			$categories[] = $category;
		}
		if ($merchant_ids) {
			$query = <<<QUERY
				SELECT
					DISTINCT
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
					COALESCE(c.minimum_purchase, a.minimum_purchase, d.value::INT) AS minimum_purchase,
					a.shipping_cost
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
				$query .= ' a.premium_merchant IS NULL AND a.id IN(' . implode(',', $merchant_ids) . ')';
			}
			$query .= ' ORDER BY a.company';
			$result = $this->db->query($query);
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($item = $result->fetch()) {
				$delivery_days = [];
				$now           = (new DateTime(null, $this->currentDatetime->getTimezone()))->setTimestamp($this->currentDatetime->getTimestamp());
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
				$delivery_hours = $item->delivery_hours ? explode(',', $item->delivery_hours) : $business_hours;
				if ($hours = explode(',', $item->delivery_hours)) {
					foreach ($business_hours as &$hour) {
						if (!in_array($hour, $hours)) {
							$hour = ',';
						} else {
							$hour .= '.00';
						}
					}
				}
				$stringified_delivery_hours = trim(preg_replace(['/\,+/', '/(0)([1-9])/', '/([1-2]?[0-9]\.00)(-[1-2]?[0-9]\.00)+(-[1-2]?[0-9]\.00)/'], [',', '\1-\2', '\1\3'], implode('', $business_hours)), ',');
				foreach ($day_aliases as $i => $alias) {
					$i && $now->modify('+1 day');
					$current_hour = $now->format('G');
					if (!$i && $current_hour >= max($delivery_hours)) {
						continue;
					}
					$current_day  = $now->format('l');
					$delivery_day = [
						'date'  => $now->format('Y-m-d'),
						'label' => $date_formatter->format($now) . ' (' . $alias . ')',
					];
					if (($current_day == 'Sunday' && !$item->open_on_sunday) ||
						($current_day == 'Monday' && !$item->open_on_monday) ||
						($current_day == 'Tuesday' && !$item->open_on_tuesday) ||
						($current_day == 'Wednesday' && !$item->open_on_wednesday) ||
						($current_day == 'Thursday' && !$item->open_on_thursday) ||
						($current_day == 'Friday' && !$item->open_on_friday) ||
						($current_day == 'Saturday' && !$item->open_on_saturday)) {
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
				$merchants[$item->id] = [
					'id'                         => $item->id,
					'company'                    => $item->company,
					'address'                    => $item->address,
					'business_days'              => trim(preg_replace(['/\,+/', '/([a-z])([A-Z])/', '/([A-Za-z]+)(-[A-Za-z]+)+(-[A-Za-z]+)/'], [',', '\1-\2', '\1\3'], implode('', $business_days)), ',') ?: '-',
					'business_opening_hour'      => $item->business_opening_hour . '.00',
					'business_closing_hour'      => $item->business_closing_hour . '.00 WIB',
					'stringified_delivery_hours' => $stringified_delivery_hours ? $stringified_delivery_hours . ' WIB' : '-',
					'minimum_purchase'           => $item->minimum_purchase,
					'shipping_cost'              => $item->shipping_cost ?? 0,
					'delivery_hours'             => $delivery_hours,
					'delivery_days'              => $delivery_days,
				];
			}
		}
		$this->_response['status'] = 1;
		$this->_response['data']   = [
			'categories' => $categories,
			'merchants'  => $merchants,
		];
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}