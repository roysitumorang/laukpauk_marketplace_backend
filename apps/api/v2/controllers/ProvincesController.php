<?php

namespace Application\Api\V2\Controllers;

use Phalcon\Db;

class ProvincesController extends ControllerBase {
	function beforeExecuteRoute() {}

	function indexAction() {
		if (!$this->cache->exists('provinces')) {
			$provinces = [];
			$query     = <<<QUERY
				SELECT
					a.id AS province_id,
					a.name AS province_name,
					b.id AS city_id,
					CONCAT_WS(' ', b.type, b.name) AS city_name,
					c.id AS subdistrict_id,
					c.name AS subdistrict_name,
					d.id AS village_id,
					d.name AS village_name
				FROM
					provinces a
					JOIN cities b ON a.id = b.province_id
					JOIN subdistricts c ON b.id = c.city_id
					JOIN villages d ON c.id = d.subdistrict_id
				WHERE
					EXISTS(SELECT 1 FROM service_areas e WHERE e.village_id = d.id
QUERY;
			if ($this->_premium_merchant) {
				$query .= " AND e.user_id = {$this->_premium_merchant->id}";
			}
			$query .= <<<QUERY
				) ORDER BY
					province_name,
					city_name,
					subdistrict_name,
					village_name
QUERY;
			$result = $this->db->query($query);
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($row = $result->fetch()) {
				if (!isset($provinces[$row->province_id])) {
					$provinces[$row->province_id] = [
						'name'   => $row->province_name,
						'cities' => [],
					];
				}
				if (!isset($provinces[$row->province_id]['cities'][$row->city_id])) {
					$provinces[$row->province_id]['cities'][$row->city_id] = [
						'name'         => $row->city_name,
						'subdistricts' => [],
					];
				}
				if (!isset($provinces[$row->province_id]['cities'][$row->city_id]['subdistricts'][$row->subdistrict_id])) {
					$provinces[$row->province_id]['cities'][$row->city_id]['subdistricts'][$row->subdistrict_id] = [
						'name'     => $row->subdistrict_name,
						'villages' => [],
					];
				}
				if (!isset($provinces[$row->province_id]['cities'][$row->city_id]['subdistricts'][$row->subdistrict_id]['villages'][$row->village_id])) {
					$provinces[$row->province_id]['cities'][$row->city_id]['subdistricts'][$row->subdistrict_id]['villages'][$row->village_id] = $row->village_name;
				}
			}
			$this->cache->save('provinces', $provinces);
		}
		$this->_response['status']            = 1;
		$this->_response['data']['provinces'] = $this->cache->get('provinces');
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}