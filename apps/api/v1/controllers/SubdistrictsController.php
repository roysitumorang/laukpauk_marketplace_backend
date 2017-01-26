<?php

namespace Application\Api\V1\Controllers;

use Application\Models\City;

class SubdistrictsController extends ControllerBase {
	function beforeExecuteRoute() {}

	function indexAction() {
		if (!$this->cache->exists('subdistricts')) {
			$subdistricts = [];
			$city         = City::findFirstByName('Medan');
			foreach ($city->subdistricts as $subdistrict) {
				$villages = [];
				foreach ($subdistrict->villages as $village) {
					$villages[$village->id] = $village->name;
				}
				$subdistricts[$subdistrict->id] = [
					'name'     => $subdistrict->name,
					'villages' => $villages,
				];
			}
			$this->cache->save('subdistricts', $subdistricts);
		}
		$this->_response = [
			'status' => 1,
			'data'   => ['subdistricts' => $this->cache->get('subdistricts')],
		];
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}