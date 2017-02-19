<?php

namespace Application\Backend\Controllers;

use Application\Models\Village;
use Application\Models\ServiceArea;
use Application\Models\Role;
use Application\Models\User;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\QueryBuilder;

class ServiceAreasController extends ControllerBase {
	private $_user;

	function onConstruct() {
		if (!$this->_user = User::findFirst(['id = ?0 AND role_id = ?1', 'bind' => [
			$this->dispatcher->getParam('user_id', 'int'),
			Role::MERCHANT,
		]])) {
			$this->flashSession->error('Member tidak ditemukan');
			$this->response->redirect('admin/users');
			$this->response->send();
			return false;
		}
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$provinces    = [];
		$cities       = [];
		$subdistricts = [];
		$villages     = [];
		$result       = $this->db->query(<<<QUERY
			SELECT
				a.id AS province_id,
				a.name AS province_name,
				b.id AS city_id,
				CONCAT_WS(' ', b.type, b.name) AS city_name,
				c.id AS subdistrict_id,
				c.name AS subdistrict_name,
				d.id AS village_id,
				d.name AS village_name
			FROM provinces a
			JOIN cities b ON a.id = b.province_id
			JOIN subdistricts c ON b.id = c.city_id
			JOIN villages d ON c.id = d.subdistrict_id
			WHERE NOT EXISTS(SELECT 1 FROM service_areas e WHERE e.village_id = d.id AND e.user_id = {$this->_user->id})
			ORDER BY province_name, city_name, subdistrict_name, village_name
QUERY
		);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$provinces[$row->province_id] = $row->province_name;
			if (!isset($cities[$row->province_id])) {
				$cities[$row->province_id] = [];
			}
			if (!isset($cities[$row->province_id][$row->city_id])) {
				$cities[$row->province_id][$row->city_id] = $row->city_name;
			}
			if (!isset($subdistricts[$row->city_id])) {
				$subdistricts[$row->city_id] = [];
			}
			if (!isset($subdistricts[$row->city_id][$row->subdistrict_id])) {
				$subdistricts[$row->city_id][$row->subdistrict_id] = $row->subdistrict_name;
			}
			if (!isset($villages[$row->subdistrict_id])) {
				$villages[$row->subdistrict_id] = [];
			}
			if (!isset($villages[$row->subdistrict_id][$row->village_id])) {
				$villages[$row->subdistrict_id][$row->village_id] = $row->village_name;
			}
		}
		$current_province_id    = array_keys($provinces)[0];
		$current_cities         = $cities[$current_province_id];
		$current_city_id        = array_keys($current_cities)[0];
		$current_subdistricts   = $subdistricts[$current_city_id];
		$current_subdistrict_id = array_keys($current_subdistricts)[0];
		$current_villages       = $villages[$current_subdistrict_id];
		$services_areas         = [];
		$builder                = $this->modelsManager->createBuilder()
			->columns([
				'province_id'      => 'a.id',
				'province_name'    => 'a.name',
				'city_id'          => 'b.id',
				'city_name'        => "CONCAT_WS(' ', b.type, b.name)",
				'subdistrict_id'   => 'c.id',
				'subdistrict_name' => 'c.name',
				'village_id'       => 'd.id',
				'village_name'     => 'd.name',
			])
			->from(['a' => 'Application\Models\Province'])
			->join('Application\Models\City', 'a.id = b.province_id', 'b')
			->join('Application\Models\Subdistrict', 'b.id = c.city_id', 'c')
			->join('Application\Models\Village', 'c.id = d.subdistrict_id', 'd')
			->join('Application\Models\ServiceArea', 'd.id = e.village_id', 'e')
			->where('e.user_id = ' . $this->_user->id)
			->orderBy('province_name, city_name, subdistrict_name, village_name');
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page  = $paginator->getPaginate();
		$pages = $this->_setPaginationRange($page);
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$services_areas[] = $item;
		}
		$this->view->menu                 = $this->_menu('Members');
		$this->view->pages                = $pages;
		$this->view->page                 = $page;
		$this->view->user                 = $this->_user;
		$this->view->service_areas        = $services_areas;
		$this->view->provinces            = $provinces;
		$this->view->cities               = $cities;
		$this->view->subdistricts         = $subdistricts;
		$this->view->villages             = $villages;
		$this->view->current_cities       = $current_cities;
		$this->view->current_subdistricts = $current_subdistricts;
		$this->view->current_villages     = $current_villages;
	}

	function createAction() {
		if ($this->request->isPost()) {
			$village_id = $this->request->getPost('village_id', 'int');
			$village    = Village::findFirstById($village_id);
			if ($village && !$this->_user->getRelated('service_areas', ['Application\Models\Village.id = ?0', 'bind' => [$village->id]])->getFirst()) {
				$service_area          = new ServiceArea;
				$service_area->user    = $this->_user;
				$service_area->village = $village;
				$service_area->create();
				$this->flashSession->success('Penambahan area operasional berhasil!');
			}
		}
		return $this->response->redirect("/admin/service_areas/index/user_id:{$this->_user->id}");
	}

	function deleteAction($id) {
		if ($this->request->isPost()) {
			if (!($service_area = ServiceArea::findFirst(['user_id = ?0 AND village_id = ?1', 'bind' => [$this->_user->id, $id]]))) {
				$this->flashSession->error('Area operasional tidak ditemukan');
			} else {
				$service_area->delete();
				$this->flashSession->success('Area operasional berhasil dihapus');
			}
		}
		return $this->response->redirect("/admin/service_areas/index/user_id:{$this->_user->id}");
	}
}