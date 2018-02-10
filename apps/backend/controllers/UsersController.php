<?php

namespace Application\Backend\Controllers;

use Application\Models\{City, LoginHistory, Province, Role, Subdistrict, User, Village};
use Phalcon\Db;
use Phalcon\Paginator\Adapter\QueryBuilder;

class UsersController extends ControllerBase {
	function indexAction() {
		$limit          = $this->config->per_page;
		$current_page   = $this->dispatcher->getParam('page', 'int', 1);
		$offset         = ($current_page - 1) * $limit;
		$users          = [];
		$user_status    = User::STATUS;
		$keyword        = strtr($this->dispatcher->getParam('keyword', 'string'), [':' => '', '/' => '']);
		$current_role   = $this->dispatcher->getParam('role_id', 'int', null);
		$current_status = $this->dispatcher->getParam('status', 'int', array_search('ACTIVE', $user_status));
		$builder        = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'a.api_key',
				'a.name',
				'a.email',
				'a.password',
				'a.address',
				'a.village_id',
				'a.mobile_phone',
				'a.status',
				'a.activated_at',
				'a.activation_token',
				'a.password_reset_token',
				'a.deposit',
				'a.company',
				'a.registration_ip',
				'a.gender',
				'a.date_of_birth',
				'a.avatar',
				'a.thumbnails',
				'a.open_on_sunday',
				'a.open_on_monday',
				'a.open_on_tuesday',
				'a.open_on_wednesday',
				'a.open_on_thursday',
				'a.open_on_friday',
				'a.open_on_saturday',
				'a.business_opening_hour',
				'a.business_closing_hour',
				'a.latitude',
				'a.longitude',
				'a.created_by',
				'a.created_at',
				'a.updated_by',
				'a.updated_at',
				'role'                     => 'b.name',
				'village'                  => 'c.name',
				'subdistrict'              => 'd.name',
				'city'                     => 'e.name',
				'province'                 => 'f.name',
				'last_login'               => 'g.sign_in_at',
			])
			->from(['a' => User::class])
			->join(Role::class, 'a.role_id = b.id', 'b')
			->leftJoin(Village::class, 'a.village_id = c.id', 'c')
			->leftJoin(Subdistrict::class, 'c.subdistrict_id = d.id', 'd')
			->leftJoin(City::class, 'd.city_id = e.id', 'e')
			->leftJoin(Province::class, 'e.province_id = f.id', 'f')
			->leftJoin(LoginHistory::class, 'a.id = g.user_id', 'g')
			->orderBy('a.id DESC')
			->where('a.status = :status:', ['status' => $current_status])
			->andWhere('NOT EXISTS(SELECT 1 FROM Application\Models\LoginHistory h WHERE g.user_id = h.user_id AND h.id > g.id)');
		if ($current_role) {
			$builder->andWhere('a.role_id = :role_id:', ['role_id' => $current_role]);
		}
		if ($keyword) {
			$builder->andWhere('a.name ILIKE :keyword: OR a.company ILIKE :keyword: OR a.mobile_phone ILIKE :keyword:', [
				'keyword' => "%{$keyword}%",
			]);
		}
		$pagination = (new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]))->getPaginate();
		foreach ($pagination->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('status', $user_status[$item->status]);
			$users[] = $item;
		}
		asort($user_status);
		$this->view->setVars([
			'menu'                  => $this->_menu('Members'),
			'users'                 => $users,
			'pages'                 => $this->_setPaginationRange($pagination),
			'pagination'            => $pagination,
			'user_status'           => $user_status,
			'current_status'        => $current_status,
			'roles'                 => Role::find(['id > 1', 'order' => 'name']),
			'current_role'          => $current_role,
			'keyword'               => $keyword,
			'total_users'           => User::count(),
			'total_pending_users'   => User::count('status = 0'),
			'total_active_users'    => User::count('status = 1'),
			'total_suspended_users' => User::count('status = -1'),
		]);
	}

	function createAction() {
		$user          = new User;
		$user->deposit = 0;
		if ($this->request->isPost()) {
			$this->_assignModelAttributes($user);
			if ($user->validation() && $user->create()) {
				$this->flashSession->success('Penambahan member berhasil.');
				return $this->response->redirect('/admin/users/index/status:0');
			}
			$this->flashSession->error('Penambahan member tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($user->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepareFormDatas($user);
	}

	function showAction($id) {
		$user = User::findFirst(['id = ?0 AND status = 1', 'bind' => [$id]]);
		if ($user->role_id == Role::BUYER) {
			$total = [
				'orders'           => $user->countBuyerOrders(),
				'pending_orders'   => $user->countBuyerOrders('status = 0'),
				'completed_orders' => $user->countBuyerOrders('status = 1'),
				'cancelled_orders' => $user->countBuyerOrders('status = -1'),
			];
		} else if ($user->role_id == Role::MERCHANT) {
			$total = [
				'orders'           => $user->countMerchantOrders(),
				'pending_orders'   => $user->countMerchantOrders('status = 0'),
				'completed_orders' => $user->countMerchantOrders('status = 1'),
				'cancelled_orders' => $user->countMerchantOrders('status = -1'),
			];
			$this->view->setVars([
				'total_products'       => $user->countProducts(),
				'total_coverage_areas' => $user->countCoverageAreas(),
			]);
		}
		if ($user->avatar) {
			$user->thumbnail = $user->getThumbnail(300, 300);
		}
		$this->view->setVars([
			'menu'       => $this->_menu('Members'),
			'user'       => $user,
			'status'     => User::STATUS,
			'last_login' => LoginHistory::maximum(['user_id = ?0', 'bind' => [$user->id], 'column' => 'sign_in_at']),
			'total'      => $total,
		]);
	}

	function updateAction($id) {
		$user = User::findFirst(['id = ?0 AND status = 1', 'bind' => [$id]]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/admin/users');
		}
		if ($this->request->isPost()) {
			$this->_assignModelAttributes($user);
			if ($user->validation() && $user->update()) {
				$this->flashSession->success('Update member berhasil.');
				return $this->response->redirect("/admin/users/{$user->id}");
			}
			$this->flashSession->error('Update member tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($user->getMessages() as $error) {
				$this->flashSession->error($error);
			}
			$user->province_id = $this->request->getPost('province_id', 'int');
			$user->city_id     = $this->request->getPost('city_id', 'int');
		}
		$this->_prepareFormDatas($user);
	}

	function deleteAvatarAction($id) {
		$user = User::findFirst(['id = ?0 AND status = 1', 'bind' => [$id]]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/admin/users');
		}
		$user->deleteAvatar();
		return $this->response->redirect("/admin/users/{$user->id}");
	}

	function activateAction($id) {
		$user = User::findFirst([
			'id = ?0 AND status = ?1',
			'bind' => [
				$id,
				array_search('HOLD', User::STATUS),
			]
		]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/admin/users');
		}
		$user->activate();
		$this->flashSession->success('Aktivasi member berhasil.');
		return $this->response->redirect('/admin/users/index/status:1#' . $user->id);
	}

	function suspendAction($id) {
		$user = User::findFirst([
			'id = ?0 AND status = ?1',
			'bind' => [
				$id,
				array_search('ACTIVE', User::STATUS),
			]
		]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/admin/users');
		}
		$user->suspend();
		$this->flashSession->success('Member berhasil dinonaktifkan.');
		return $this->response->redirect('/admin/users/index/status:-1#' . $user->id);
	}

	function reactivateAction($id) {
		$user = User::findFirst([
			'id = ?0 AND status = ?1',
			'bind' => [
				$id,
				array_search('SUSPENDED', User::STATUS),
			]
		]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/admin/users');
		}
		$user->reactivate();
		$this->flashSession->success('Member berhasil diaktifkan kembali.');
		return $this->response->redirect('/admin/users/index/status:1#' . $user->id);
	}

	function excelAction() {
		$this->response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
		$this->response->setHeader('Content-Type', 'application/octet-stream');
		$this->response->setHeader('Content-Disposition', 'attachment; filename=users.csv');
		$output = fopen('php://output', 'w');
		foreach (User::find(['status = 1', 'columns' => 'name, mobile_phone', 'order' => 'name']) as $user) {
			fputs($output, sprintf('"%s","","%s"' . "\r\n", strtr($user->name, '"', '\"'), preg_replace('/[^\d]+/', '', $user->mobile_phone)));
		}
		fclose($output);
		$this->response->send();
		exit;
	}

	function citiesAction($province_id) {
		$cities = [];
		$result = $this->db->query(<<<QUERY
			SELECT
				a.id,
				CONCAT_WS(' ', a.type, a.name) AS name
			FROM
				cities a
				JOIN subdistricts b ON a.id = b.city_id
				JOIN villages c ON b.id = c.subdistrict_id
			WHERE
				a.province_id = {$province_id}
			GROUP BY a.id
			ORDER BY name
QUERY
		);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$cities[] = $row;
		}
		$this->response->setJsonContent($cities, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function subdistrictsAction($city_id) {
		$subdistricts = [];
		$result       = $this->db->query(<<<QUERY
			SELECT
				a.id,
				a.name
			FROM
				subdistricts a
				JOIN villages b ON a.id = b.subdistrict_id
			WHERE
				a.city_id = {$city_id}
			GROUP BY a.id
			ORDER BY a.name
QUERY
		);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$subdistricts[] = $row;
		}
		$this->response->setJsonContent($subdistricts, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function villagesAction($subdistrict_id) {
		$villages = [];
		$result   = $this->db->query(<<<QUERY
			SELECT
				id,
				name
			FROM
				villages
			WHERE
				subdistrict_id = {$subdistrict_id}
			ORDER BY name
QUERY
		);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$villages[] = $row;
		}
		$this->response->setJsonContent($villages, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	private function _prepareFormDatas(User $user) {
		$provinces      = [];
		$cities         = [];
		$subdistricts   = [];
		$villages       = [];
		$business_hours = [];
		$result         = $this->db->query(<<<QUERY
			SELECT
				a.id,
				a.name
			FROM
				provinces a
				JOIN cities b ON a.id = b.province_id
				JOIN subdistricts c ON b.id = c.city_id
				JOIN villages d ON c.id = d.subdistrict_id
			GROUP BY a.id
			ORDER BY a.name
QUERY
		);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$provinces[$row->id] = $row->name;
		}
		if ($province_id = $this->request->getPost('province_id', 'int', $user->village->subdistrict->city->province_id)) {
			$result = $this->db->query(<<<QUERY
				SELECT
					a.id,
					CONCAT_WS(' ', a.type, a.name) AS name
				FROM
					cities a
					JOIN subdistricts b ON a.id = b.city_id
					JOIN villages c ON b.id = c.subdistrict_id
				WHERE a.province_id = {$province_id}
				GROUP BY a.id
				ORDER BY name
QUERY
			);
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($row = $result->fetch()) {
				$cities[$row->id] = $row->name;
			}
		}
		if ($city_id = $this->request->getPost('city_id', 'int', $user->village->subdistrict->city_id)) {
			$result  = $this->db->query(<<<QUERY
				SELECT
					a.id,
					a.name
				FROM
					subdistricts a
					JOIN villages b ON a.id = b.subdistrict_id
				WHERE a.city_id = {$city_id}
				GROUP BY a.id
				ORDER BY a.name
QUERY
			);
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($row = $result->fetch()) {
				$subdistricts[$row->id] = $row->name;
			}
		}
		if ($subdistrict_id = $this->request->getPost('subdistrict_id', 'int', $user->village->subdistrict_id)) {
			$result = $this->db->query(<<<QUERY
				SELECT
					id,
					name
				FROM
					villages
				WHERE subdistrict_id = {$subdistrict_id}
				ORDER BY name
QUERY
			);
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($row = $result->fetch()) {
				$villages[$row->id] = $row->name;
			}
		}
		foreach (range(User::BUSINESS_HOURS['opening'], User::BUSINESS_HOURS['closing']) as $hour) {
			$business_hours[$hour] = ($hour < 10 ? '0' . $hour : $hour) . ':00';
		}
		$this->view->setVars([
			'menu'           => $this->_menu('Members'),
			'user'           => $user,
			'status'         => User::STATUS,
			'genders'        => User::GENDERS,
			'business_hours' => $business_hours,
			'provinces'      => $provinces,
			'cities'         => $cities,
			'subdistricts'   => $subdistricts,
			'villages'       => $villages,
			'province_id'    => $province_id,
			'city_id'        => $city_id,
			'subdistrict_id' => $subdistrict_id,
			'roles'          => Role::find([
				'id IN ({ids:array})',
				'bind'  => ['ids' => [Role::ADMIN, Role::MERCHANT, Role::BUYER]],
				'order' => 'name',
			]),
		]);
	}

	private function _assignModelAttributes(User &$user) {
		if (($village_id = $this->request->getPost('village_id', 'int')) && ($village = Village::findFirst($village_id))) {
			$user->village_id     = $village->id;
			$user->subdistrict_id = $village->subdistrict_id;
		}
		if (($role_id = $this->request->getPost('role_id', 'int')) && Role::count(['id = ?0', 'bind' => [$role_id]])) {
			$user->role_id = $role_id;
		}
		$user->assign($this->request->getPost(), null, [
			'minimum_purchase',
			'admin_fee',
			'accumulation_divisor',
			'name',
			'email',
			'address',
			'mobile_phone',
			'company',
			'gender',
			'date_of_birth',
			'open_on_sunday',
			'open_on_monday',
			'open_on_tuesday',
			'open_on_wednesday',
			'open_on_thursday',
			'open_on_friday',
			'open_on_saturday',
			'business_opening_hour',
			'business_closing_hour',
			'merchant_note',
		]);
		if ($user->role_id == Role::MERCHANT) {
			$user->assign($this->request->getPost(), null, [
				'deposit',
				'delivery_hours',
				'delivery_max_distance',
				'delivery_free_distance',
				'delivery_rate',
			]);
		}
		$user->setNewPassword($this->request->getPost('new_password'));
		$user->setNewPasswordConfirmation($this->request->getPost('new_password_confirmation'));
		if ($this->request->hasFiles()) {
			$user->setNewAvatar(current(array_filter($this->request->getUploadedFiles(), function(&$v, $k) {
				return $v->getKey() == 'new_avatar';
			}, ARRAY_FILTER_USE_BOTH)));
		}
	}
}