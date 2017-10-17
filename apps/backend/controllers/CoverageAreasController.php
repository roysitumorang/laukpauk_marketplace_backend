<?php

namespace Application\Backend\Controllers;

use Application\Models\{City, CoverageArea, Province, Role, Subdistrict, User, Village};
use Phalcon\Db;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\QueryBuilder;

class CoverageAreasController extends ControllerBase {
	private $_user;

	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
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
		$this->_render(new CoverageArea);
	}

	function createAction() {
		$coverage_area = new CoverageArea;
		if ($this->request->isPost()) {
			$coverage_area->user_id    = $this->_user->id;
			$coverage_area->village_id = Village::findFirstById($this->request->getPost('village_id', 'int'))->id;
			$coverage_area->setShippingCost($this->request->getPost('shipping_cost', 'int', 0));
			if ($coverage_area->validation() && $coverage_area->create()) {
				$this->flashSession->success('Penambahan area operasional berhasil!');
				return $this->response->redirect("/admin/users/{$this->_user->id}/coverage_areas");
			}
			foreach ($coverage_area->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_render($coverage_area);
	}

	function updateAction() {
		$page = $this->dispatcher->getParam('page', 'int', 1);
		if ($this->request->isPost()) {
			foreach ($this->request->getPost('shipping_cost') as $id => $shippingCost) {
				$coverage_area = CoverageArea::findFirst(['user_id = ?0 AND id = ?1', 'bind' => [$this->_user->id, $id]]);
				if ($coverage_area) {
					$coverage_area->setShippingCost($shippingCost);
					$coverage_area->update();
				}
			}
			$this->flashSession->success('Update area operasional berhasil!');
			return $this->response->redirect("/admin/users/{$this->_user->id}/coverage_areas" . ($page > 1 ? '/index/page:' . $page : ''));
		}
		$this->_render($coverage_area);
	}


	function deleteAction($id) {
		$page = $this->dispatcher->getParam('page', 'int', 1);
		if ($this->request->isPost()) {
			if (!($coverage_area = CoverageArea::findFirst(['user_id = ?0 AND village_id = ?1', 'bind' => [$this->_user->id, $id]]))) {
				$this->flashSession->error('Area operasional tidak ditemukan');
			} else {
				$coverage_area->delete();
				$this->flashSession->success('Area operasional berhasil dihapus');
			}
		}
		return $this->response->redirect("/admin/users/{$this->_user->id}/coverage_areas" . ($page > 1 ? '/index/page:' . $page : ''));
	}

	function villagesAction($subdistrictId) {
		$villages = [];
		$result   = $this->db->query('SELECT a.id, a.name FROM villages a WHERE a.subdistrict_id = ? AND EXISTS(SELECT 1 FROM subdistricts b WHERE a.subdistrict_id = b.id AND b.city_id = ?) AND NOT EXISTS(SELECT 1 FROM coverage_area c WHERE c.village_id = a.id AND c.user_id = ?) ORDER BY name', [
			$subdistrictId,
			$this->_user->village->subdistrict->city_id,
			$this->_user->id,
		]);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$villages[] = $row;
		}
		$this->response->setJsonContent($villages, JSON_NUMERIC_CHECK);
		return $this->response;
	}

	private function _render(CoverageArea $coverage_area = null) {
		$coverage_areas = [];
		$subdistricts   = [];
		$villages       = [];
		$limit          = $this->config->per_page;
		$current_page   = $this->dispatcher->getParam('page', 'int', 1);
		$offset         = ($current_page - 1) * $limit;
		$result         = $this->db->query('SELECT a.id, a.name FROM subdistricts a JOIN villages b ON a.id = b.subdistrict_id WHERE a.city_id = ? AND NOT EXISTS(SELECT 1 FROM coverage_area c WHERE c.village_id = b.id AND c.user_id = ?) GROUP BY a.id ORDER BY name', [
			$this->_user->village->subdistrict->city_id,
			$this->_user->id,
		]);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$subdistricts[$row->id] = $row->name;
		}
		$result = $this->db->query('SELECT a.id, a.name FROM villages a WHERE a.subdistrict_id = ? AND NOT EXISTS(SELECT 1 FROM coverage_area b WHERE b.village_id = a.id AND b.user_id = ?) ORDER BY name', [
			$this->_user->village->subdistrict_id,
			$this->_user->id,
		]);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$villages[$row->id] = $row->name;
		}
		$builder = $this->modelsManager->createBuilder()
			->columns([
				'e.id',
				'province_id'      => 'a.id',
				'province_name'    => 'a.name',
				'city_id'          => 'b.id',
				'city_name'        => "CONCAT_WS(' ', b.type, b.name)",
				'subdistrict_id'   => 'c.id',
				'subdistrict_name' => 'c.name',
				'village_id'       => 'd.id',
				'village_name'     => 'd.name',
				'e.shipping_cost',
			])
			->from(['a' => Province::class])
			->join(City::class, 'a.id = b.province_id', 'b')
			->join(Subdistrict::class, 'b.id = c.city_id', 'c')
			->join(Village::class, 'c.id = d.subdistrict_id', 'd')
			->join(CoverageArea::class, 'd.id = e.village_id', 'e')
			->where('e.user_id = ' . $this->_user->id)
			->orderBy('province_name, city_name, subdistrict_name, village_name');
		$pagination = (new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]))->getPaginate();
		foreach ($pagination->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$coverage_areas[] = $item;
		}
		$this->view->setVars([
			'menu'           => $this->_menu('Members'),
			'pages'          => $this->_setPaginationRange($pagination),
			'pagination'     => $pagination,
			'user'           => $this->_user,
			'coverage_areas' => $coverage_areas,
			'subdistricts'   => $subdistricts,
			'villages'       => $villages,
			'coverage_area'  => $coverage_area,
		]);
	}
}
