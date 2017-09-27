<?php

namespace Application\Api\V3\Buyer;

use Ds\Set;
use Phalcon\Db;

class SalePackagesController extends ControllerBase {
	function indexAction() {
		$page          = $this->dispatcher->getParam('page', 'int');
		$search_query  = $this->dispatcher->getParam('keyword', 'string') ?: null;
		$limit         = 10;
		$params        = [];
		$sale_packages = [];
		$merchant_ids  = new Set;
		$merchants     = [];
		$stop_words    = preg_split('/,/', $this->db->fetchColumn("SELECT value FROM settings WHERE name = 'stop_words'"), -1, PREG_SPLIT_NO_EMPTY);
		$keywords      = '';
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
				sale_packages a
				JOIN coverage_area b USING(user_id)
				LEFT JOIN sale_package_product c ON a.id = c.sale_package_id
				LEFT JOIN user_product d ON c.user_product_id = d.id
				LEFT JOIN products e ON d.product_id = e.id
			WHERE
				b.village_id = {$this->_current_user->village->id} AND
				a.published = '1'
QUERY;
		if ($search_query) {
			if ($this->db->fetchColumn("SELECT COUNT(1) FROM sale_packages a JOIN coverage_area b USING(user_id) WHERE b.village_id = {$this->_current_user->village->id} AND a.published = '1' AND a.name = ?", [$search_query])) {
				$query   .= ' AND a.name = ?';
				$params[] = $search_query;
			} else if ($keywords) {
				$query .= " AND a.keywords @@ TO_TSQUERY('{$keywords}')";
			}
		}
		$total_products = $this->db->fetchColumn($query, $params);
		$total_pages    = ceil($total_products / $limit);
		$current_page   = $page > 0 ? $page : 1;
		$offset         = ($current_page - 1) * $limit;
		$result         = $this->db->query(strtr($query, ['COUNT(DISTINCT a.id)' => <<<QUERY
			a.id,
			a.user_id,
			a.name,
			a.price,
			a.stock,
			a.picture,
			TS_RANK(a.keywords, TO_TSQUERY('{$keywords}')) AS relevancy,
			STRING_AGG(e.name || ' (' || e.stock_unit || ') x ' || c.quantity, ',') AS products
QUERY
		]) . " GROUP BY a.id ORDER BY relevancy DESC, a.name LIMIT {$limit} OFFSET {$offset}", $params);
		$picture_root_url = 'http' . ($this->request->getScheme() === 'https' ? 's' : '') . '://' . $this->request->getHttpHost() . '/assets/image/';
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$merchant_ids->contains($row->user_id) || $merchant_ids->add($row->user_id);
			if ($row->picture) {
				$row->thumbnail = $picture_root_url . strtr($row->picture, ['.jpg' => '120.jpg']);
				$row->picture   = $picture_root_url . strtr($row->picture, ['.jpg' => '300.jpg']);
			}
			$row->products = explode(',', $row->products);
			unset($row->picture, $row->relevancy);
			$sale_packages[] = $row;
		}
		if (!$merchant_ids->isEmpty()) {
			$today    = $this->currentDatetime->format('N');
			$tomorrow = $this->currentDatetime->modify('+1 day')->format('N');
			$query    = sprintf(<<<QUERY
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
					a.minimum_purchase,
					c.shipping_cost,
					a.merchant_note
				FROM
					users a
					JOIN roles b ON a.role_id = b.id
					JOIN coverage_area c ON a.id = c.user_id
					JOIN user_product d ON a.id = d.user_id
					JOIN products e ON d.product_id = e.id
					JOIN product_categories f ON e.product_category_id = f.id
				WHERE
					a.status = 1 AND
					b.name = 'Merchant' AND
					c.village_id = {$this->_current_user->village->id} AND
					a.id IN(%s)
				ORDER BY a.company
QUERY
				, $merchant_ids->join(','));
			$result = $this->db->query($query);
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($item = $result->fetch()) {
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
				$delivery_hours       = trim(preg_replace(['/\,+/', '/(0)([1-9])/', '/([1-2]?[0-9]\.00)(-[1-2]?[0-9]\.00)+(-[1-2]?[0-9]\.00)/'], [',', '\1-\2', '\1\3'], implode('', $business_hours)), ',');
				$merchants[$item->id] = [
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
		}
		if (!$total_products) {
			if ($keywords) {
				$this->_response['message'] = 'Paket belanja tidak ditemukan.';
			} else if (!$total_pages) {
				$this->_response['message'] = 'Paket belanja belum ada.';
			}
		} else {
			$this->_response['status'] = 1;
		}
		$this->_response['data']['sale_packages'] = $sale_packages;
		$this->_response['data']['current_hour']  = $this->currentDatetime->format('G');
		$this->_response['data']['merchants']     = $merchants;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}