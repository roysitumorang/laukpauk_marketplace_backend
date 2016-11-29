<?php

namespace Application\Backend\Controllers;

use Application\Models\Village;
use Application\Models\ServiceArea;
use Application\Models\User;
use Exception;
use Phalcon\Db;

class ServiceAreasController extends BaseController {
	private $_user;

	function onConstruct() {
		try {
			if (!($user_id = $this->dispatcher->getParam('user_id', 'int')) || !($this->_user = User::findFirst($user_id))) {
				throw new Exception('Data tidak ditemukan');
			}
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
			return $this->response->redirect('/admin/users');
		}
	}

	function indexAction() {
		$cached_subdistricts          = apcu_fetch('subdistricts');
		$cached_villages              = apcu_fetch('villages');
		$subdistricts                 = [];
		$villages                     = [];
		$existing_village_ids         = [];
		$services_areas               = [];
		$result                       = $this->db->query("SELECT a.id, a.village_id, b.name AS village, c.name AS subdistrict FROM service_areas a JOIN villages b ON a.village_id = b.id JOIN subdistricts c ON b.subdistrict_id = c.id WHERE a.user_id = {$this->_user->id} ORDER BY CONCAT(c.name, b.name)");
		$result->setFetchMode(Db::FETCH_OBJ);
		$i                            = 0;
		while ($service_area = $result->fetch()) {
			$service_area->rank = ++$i;
			$services_areas[]                                = $service_area;
			$existing_village_ids[$service_area->village_id] = 1;
		}
		foreach ($cached_subdistricts as $subdistrict) {
			$subdistrict_villages = [];
			foreach ($cached_villages[$subdistrict->id] as $village) {
				if (!isset($existing_village_ids[$village->id])) {
					$subdistrict_villages[] = $village;
				}
			}
			if ($subdistrict_villages) {
				$subdistricts[]             = $subdistrict;
				$villages[$subdistrict->id] = $subdistrict_villages;
			}
		}
		$this->view->menu             = $this->_menu('Members');
		$this->view->user             = $this->_user;
		$this->view->service_areas    = $services_areas;
		$this->view->subdistricts     = $subdistricts;
		$this->view->current_villages = $villages[$subdistricts[0]->id];
		$this->view->villages_json    = json_encode($villages, JSON_NUMERIC_CHECK);
	}

	function createAction() {
		try {
			if (!$this->request->isPost()) {
				throw new Exception('Request tidak valid');
			}
			$village_id = $this->request->getPost('village_id', 'int');
			if ($village_id && ($village = Village::findFirst($village_id)) && !$this->_user->getRelated('service_areas', ['village_id = :village_id:', 'bind' => ['village_id' => $village->id]])->getFirst()) {
				$new_service_areas          = [];
				foreach ($this->_user->getRelated('service_areas') as $service_area) {
					$new_service_areas[] = $service_area;
				}
				$service_area               = new ServiceArea;
				$service_area->village      = $village;
				$new_service_areas[]        = $service_area;
				$this->_user->service_areas = $new_service_areas;
				$this->_user->save();
				$this->flashSession->success('Penambahan area operasional berhasil');
			}
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		}
		return $this->response->redirect("/admin/service_areas/index/user_id:{$this->_user->id}");
	}

	function deleteAction($id) {
		try {
			if (!$this->request->isPost()) {
				throw new Exception('Request tidak valid');
			}
			$service_area = $this->_user->getRelated('service_areas', ['id = :id:', 'bind' => ['id' => $id]])->getFirst();
			if (!$service_area) {
				throw new Exception('Data tidak ditemukan');
			}
			$service_area->delete();
			$this->flashSession->success('Area operasional berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		}
		return $this->response->redirect("/admin/service_areas/index/user_id:{$this->_user->id}");
	}
}