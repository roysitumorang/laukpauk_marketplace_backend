<?php

namespace Application\Api\V1\Controllers;

use Application\Models\City;

class SubdistrictsController extends ControllerBase {
	function beforeExecuteRoute() {}

	function indexAction() {
		$this->_response['status'] = 1;
		$subdistricts              = [];
		$city                      = City::findFirstByName('Medan');
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
		$this->_response['data']['subdistricts'] = $subdistricts;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK);
		return $this->response;
	}
}