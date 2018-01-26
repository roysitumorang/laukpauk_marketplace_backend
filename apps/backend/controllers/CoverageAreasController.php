<?php

namespace Application\Backend\Controllers;

use Application\Models\{City, CoverageArea, Province, Subdistrict, User, Village};
use Phalcon\Paginator\Adapter\QueryBuilder;

class CoverageAreasController extends ControllerBase {
	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		if (!$this->persistent->user = User::findFirst(['id = ?0 AND role_id = 3', 'bind' => [$this->dispatcher->getParam('user_id', 'int!')]])) {
			$this->flashSession->error('Merchant tidak ditemukan');
			$this->response->redirect('/admin/users');
			$this->response->send();
			return false;
		}
		$this->persistent->page        = $this->dispatcher->getParam('page', 'int!', 1);
		$this->persistent->index_route = "/admin/users/{$this->persistent->user->id}/coverage_areas" . ($this->persistent->page > 1 ? '/index/page:' . $this->persistent->page : '');

	}

	function indexAction() {
		$this->_render(new CoverageArea);
	}

	function createAction() {
		$coverage_area = new CoverageArea;
		if ($this->request->isPost()) {
			$coverage_area->user_id    = $this->persistent->user->id;
			$coverage_area->village_id = Village::findFirstById($this->request->getPost('village_id', 'int!'))->id;
			$coverage_area->setShippingCost($this->request->getPost('shipping_cost', 'int!', 0));
			if ($coverage_area->validation() && $coverage_area->create()) {
				$coverage_area = new CoverageArea;
				$this->flashSession->success('Penambahan area operasional berhasil!');
			} else {
				foreach ($coverage_area->getMessages() as $error) {
					$this->flashSession->error($error);
				}
			}
		}
		$this->_render($coverage_area);
	}

	function updateAction() {
		if ($this->request->isPost() && ($shipping_costs = $this->request->getPost('shipping_cost'))) {
			$coverage_areas = CoverageArea::find(['user_id = ?0 AND id IN({ids:array})', 'bind' => [$this->persistent->user->id, 'ids' => array_keys($shipping_costs)]]);
			foreach ($coverage_areas as $coverage_area) {
				$coverage_area->setShippingCost($shipping_costs[$coverage_area->id]);
				$coverage_area->update();
			}
			$this->flashSession->success('Update area operasional berhasil!');
			return $this->response->redirect($this->persistent->index_route);
		}
		$this->_render(new CoverageArea);
	}

	function deleteAction($id) {
		if (!$coverage_area = CoverageArea::findFirst(['user_id = ?0 AND village_id = ?1', 'bind' => [$this->persistent->user->id, $id]])) {
			$this->flashSession->error('Area operasional tidak ditemukan');
		} else {
			$coverage_area->delete();
			$this->flashSession->success('Area operasional berhasil dihapus');
		}
		return $this->response->redirect($this->persistent->index_route);
	}

	function citiesAction($province_id) {
		$cities = [];
		$result = City::find(['province_id = ?0 AND EXISTS(SELECT 1 FROM [Application\Models\Subdistrict] JOIN [Application\Models\Village] ON [Application\Models\Subdistrict].id = [Application\Models\Village].subdistrict_id WHERE [Application\Models\Subdistrict].city_id = [Application\Models\City].id AND NOT EXISTS(SELECT 1 FROM [Application\Models\CoverageArea] WHERE [Application\Models\CoverageArea].village_id = [Application\Models\Village].id AND [Application\Models\CoverageArea].user_id = ?1))', 'bind' => [$province_id, $this->persistent->user->id], 'columns' => 'id, type, name', 'order' => 'type, name']);
		foreach ($result as $city) {
			$cities[] = [
				'id'   => $city->id,
				'name' => $city->type . ' ' . $city->name,
			];
		}
		$this->response->setJsonContent($cities);
		return $this->response;
	}

	function subdistrictsAction($city_id) {
		$subdistricts = [];
		$result       = Subdistrict::find(['city_id = ?0 AND EXISTS(SELECT 1 FROM [Application\Models\Village] WHERE [Application\Models\Village].subdistrict_id = [Application\Models\Subdistrict].id AND NOT EXISTS(SELECT 1 FROM [Application\Models\CoverageArea] WHERE [Application\Models\CoverageArea].village_id = [Application\Models\Village].id AND [Application\Models\CoverageArea].user_id = ?1))', 'bind' => [$city_id, $this->persistent->user->id], 'columns' => 'id, name', 'order' => 'name']);
		foreach ($result as $subdistrict) {
			$subdistricts[] = $subdistrict;
		}
		$this->response->setJsonContent($subdistricts);
		return $this->response;
	}

	function villagesAction($subdistrict_id) {
		$villages = [];
		$result   = Village::find(['subdistrict_id = ?0 AND NOT EXISTS(SELECT 1 FROM [Application\Models\CoverageArea] WHERE [Application\Models\CoverageArea].village_id = [Application\Models\Village].id AND [Application\Models\CoverageArea].user_id = ?1)', 'bind' => [$subdistrict_id, $this->persistent->user->id], 'columns' => 'id, name', 'order' => 'name']);
		foreach ($result as $village) {
			$villages[] = $village;
		}
		$this->response->setJsonContent($villages);
		return $this->response;
	}

	private function _render(CoverageArea $coverage_area) {
		$coverage_areas = [];
		$province_id    = $this->request->getPost('province_id', 'int!');
		$city_id        = $this->request->getPost('city_id', 'int!');
		$subdistrict_id = $this->request->getPost('subdistrict_id', 'int!');
		$provinces      = Province::find(['EXISTS(SELECT 1 FROM [Application\Models\City] JOIN [Application\Models\Subdistrict] ON [Application\Models\City].id = [Application\Models\Subdistrict].city_id JOIN [Application\Models\Village] ON [Application\Models\Subdistrict].id = [Application\Models\Village].subdistrict_id WHERE [Application\Models\City].province_id = [Application\Models\Province].id AND NOT EXISTS(SELECT 1 FROM [Application\Models\CoverageArea] WHERE [Application\Models\CoverageArea].village_id = [Application\Models\Village].id AND [Application\Models\CoverageArea].user_id = ?0))', 'bind' => [$this->persistent->user->id], 'columns' => 'id, name', 'order' => 'name']);
		$cities         = [];
		$subdistricts   = [];
		$villages       = [];
		$limit          = $this->config->per_page;
		$offset         = ($this->persistent->page - 1) * $limit;
		$builder        = $this->modelsManager->createBuilder()
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
			->where('e.user_id = ' . $this->persistent->user->id)
			->orderBy('province_name, city_name, subdistrict_name, village_name');
		$pagination = (new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $this->persistent->page,
		]))->getPaginate();
		foreach ($pagination->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$coverage_areas[] = $item;
		}
		if ($province_id) {
			$result = City::find(['province_id = ?0 AND EXISTS(SELECT 1 FROM [Application\Models\Subdistrict] JOIN [Application\Models\Village] ON [Application\Models\Subdistrict].id = [Application\Models\Village].subdistrict_id WHERE [Application\Models\Subdistrict].city_id = [Application\Models\City].id AND NOT EXISTS(SELECT 1 FROM [Application\Models\CoverageArea] WHERE [Application\Models\CoverageArea].village_id = [Application\Models\Village].id AND [Application\Models\CoverageArea].user_id = ?1))', 'bind' => [$province_id, $this->persistent->user->id], 'columns' => 'id, type, name', 'order' => 'type, name']);
			foreach ($result as $city) {
				$cities[$city->id] = $city->type . ' ' . $city->name;
			}
		}
		if ($city_id) {
			$result = Subdistrict::find(['city_id = ?0 AND EXISTS(SELECT 1 FROM [Application\Models\Village] WHERE [Application\Models\Village].subdistrict_id = [Application\Models\Subdistrict].id AND NOT EXISTS(SELECT 1 FROM [Application\Models\CoverageArea] WHERE [Application\Models\CoverageArea].village_id = [Application\Models\Village].id AND [Application\Models\CoverageArea].user_id = ?1))', 'bind' => [$city_id, $this->persistent->user->id], 'columns' => 'id, name', 'order' => 'name']);
			foreach ($result as $subdistrict) {
				$subdistricts[$subdistrict->id] = $subdistrict->name;
			}
		}
		if ($subdistrict_id) {
			$result = Village::find(['subdistrict_id = ?0 AND NOT EXISTS(SELECT 1 FROM [Application\Models\CoverageArea] WHERE [Application\Models\CoverageArea].village_id = [Application\Models\Village].id AND [Application\Models\CoverageArea].user_id = ?1)', 'bind' => [$subdistrict_id, $this->persistent->user->id], 'columns' => 'id, name', 'order' => 'name']);
			foreach ($result as $village) {
				$villages[$village->id] = $village->name;
			}
		}
		$this->view->setVars([
			'menu'           => $this->_menu('Members'),
			'pages'          => $this->_setPaginationRange($pagination),
			'pagination'     => $pagination,
			'user'           => $this->persistent->user,
			'coverage_areas' => $coverage_areas,
			'provinces'      => $provinces,
			'cities'         => $cities,
			'subdistricts'   => $subdistricts,
			'villages'       => $villages,
			'coverage_area'  => $coverage_area,
			'province_id'    => $province_id,
			'city_id'        => $city_id,
			'subdistrict_id' => $subdistrict_id,
		]);
	}
}
