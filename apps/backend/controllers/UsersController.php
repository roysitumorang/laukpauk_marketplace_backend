<?php

namespace Application\Backend\Controllers;

use Application\Models\LoginHistory;
use Application\Models\Role;
use Application\Models\User;
use Application\Models\Village;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\QueryBuilder;

class UsersController extends ControllerBase {
	function indexAction() {
		$limit          = $this->config->per_page;
		$current_page   = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset         = ($current_page - 1) * $limit;
		$status         = User::STATUS;
		$current_status = $this->dispatcher->getParam('status', 'int');
		if ($current_status === null || !array_key_exists($current_status, $status)) {
			$current_status = array_search('ACTIVE', $status);
		}
		$current_role = $this->dispatcher->getParam('role_id', 'int');
		$keyword      = strtr($this->dispatcher->getParam('keyword', 'string'), [':' => '', '/' => '']);
		$users        = [];
		$builder      = $this->modelsManager->createBuilder()
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
			->from(['a' => 'Application\Models\User'])
			->join('Application\Models\Role', 'a.role_id = b.id', 'b')
			->leftJoin('Application\Models\Village', 'a.village_id = c.id', 'c')
			->leftJoin('Application\Models\Subdistrict', 'c.subdistrict_id = d.id', 'd')
			->leftJoin('Application\Models\City', 'd.city_id = e.id', 'e')
			->leftJoin('Application\Models\Province', 'e.province_id = f.id', 'f')
			->leftJoin('Application\Models\LoginHistory', 'a.id = g.user_id', 'g')
			->orderBy('a.id DESC')
			->where('a.status = ' . $current_status)
			->andWhere('NOT EXISTS(SELECT 1 FROM Application\Models\LoginHistory h WHERE g.user_id = h.user_id AND h.id > g.id)');
		if ($current_role) {
			$builder->andWhere('a.role_id = ' . $current_role);
		}
		if ($keyword) {
			$keyword_placeholder = "%{$keyword}%";
			$builder->andWhere('a.name ILIKE ?0 OR a.company ILIKE ?1 OR a.mobile_phone ILIKE ?2', [
				$keyword_placeholder,
				$keyword_placeholder,
				$keyword_placeholder,
			]);
		}
		$paginator = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page  = $paginator->getPaginate();
		$pages = $this->_setPaginationRange($page);
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('status', $status[$item->status]);
			$users[] = $item;
		}
		$this->view->menu                     = $this->_menu('Members');
		$this->view->users                    = $users;
		$this->view->pages                    = $pages;
		$this->view->page                     = $paginator->getPaginate();
		$this->view->status                   = $status;
		$this->view->current_status           = $current_status;
		$this->view->roles                    = Role::find(['id > 1', 'order' => 'name']);
		$this->view->current_role             = $current_role;
		$this->view->keyword                  = $keyword;
		$this->view->total_users              = $this->db->fetchColumn('SELECT COUNT(1) FROM users');
		$this->view->total_pending_users      = $this->db->fetchColumn('SELECT COUNT(1) FROM users WHERE status = 0');
		$this->view->total_active_users       = $this->db->fetchColumn('SELECT COUNT(1) FROM users WHERE status = 1');
		$this->view->total_suspended_users    = $this->db->fetchColumn('SELECT COUNT(1) FROM users WHERE status = -1');
	}

	function createAction() {
		$user          = new User;
		$user->deposit = 0;
		if ($this->request->isPost()) {
			$this->_set_model_attributes($user);
			if ($user->validation() && $user->create()) {
				$this->flashSession->success('Penambahan member berhasil.');
				return $this->response->redirect('/admin/users?status=0');
			}
			$this->flashSession->error('Penambahan member tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($user->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_form_datas($user);
	}

	function showAction($id) {
		$user = User::findFirst(['id = ?0 AND status = 1', 'bind' => [$id]]);
		if ($user->role->name == 'Buyer') {
			$column = 'buyer_id';
		} else if ($user->role->name == 'Merchant') {
			$column                           = 'merchant_id';
			$this->view->total_products       = $user->countProducts();
			$this->view->total_coverage_areas = $user->countCoverageAreas();
		}
		if ($column) {
			$total = $this->db->fetchOne("
				SELECT
					COUNT(DISTINCT a.id) AS orders,
					COUNT(DISTINCT b.id) AS pending_orders,
					COUNT(DISTINCT c.id) AS completed_orders,
					COUNT(DISTINCT d.id) AS cancelled_orders
				FROM
					orders a
					LEFT JOIN orders b ON a.{$column} = b.{$column} AND b.status = 0
					LEFT JOIN orders c ON a.{$column} = c.{$column} AND c.status = 1
					LEFT JOIN orders d ON a.{$column} = d.{$column} AND d.status = -1
				WHERE
					a.{$column} = ?", Db::FETCH_OBJ, [$user->id]);
			$this->view->total = $total;
		}
		if ($user->avatar) {
			$user->thumbnail = $user->getThumbnail(300, 300);
		}
		$this->view->menu       = $this->_menu('Members');
		$this->view->user       = $user;
		$this->view->status     = User::STATUS;
		$this->view->last_login = LoginHistory::maximum(['user_id = ?0', 'bind' => [$user->id], 'column' => 'sign_in_at']);
	}

	function updateAction($id) {
		$user = User::findFirst(['id = ?0 AND status = 1', 'bind' => [$id]]);
		if (!$user) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->response->redirect('/admin/users');
		}
		if ($this->request->isPost()) {
			$this->_set_model_attributes($user);
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
		$this->_prepare_form_datas($user);
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

	private function _prepare_form_datas(User $user) {
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
			$provinces[] = $row;
		}
		if ($province_id = $user->village->subdistrict->city->province_id ?: $this->request->getPost('province_id', 'int')) {
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
				$cities[] = $row;
			}
		}
		if ($city_id = $user->village->subdistrict->city_id ?: $this->request->getPost('city_id', 'int')) {
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
				$subdistricts[] = $row;
			}
		}
		if ($subdistrict_id = $user->village->subdistrict_id ?: $this->request->getPost('subdistrict_id', 'int')) {
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
				$villages[] = $row;
			}
		}
		foreach (range(User::BUSINESS_HOURS['opening'], User::BUSINESS_HOURS['closing']) as $hour) {
			$business_hours[$hour] = ($hour < 10 ? '0' . $hour : $hour) . ':00';
		}
		$this->view->menu  = $this->_menu('Members');
		$this->view->roles = Role::find([
			'id IN ({ids:array})',
			'bind'  => ['ids' => [Role::ADMIN, Role::MERCHANT, Role::BUYER]],
			'order' => 'name',
		]);
		$this->view->user           = $user;
		$this->view->status         = User::STATUS;
		$this->view->genders        = User::GENDERS;
		$this->view->business_hours = $business_hours;
		$this->view->provinces      = $provinces;
		$this->view->cities         = $cities;
		$this->view->subdistricts   = $subdistricts;
		$this->view->villages       = $villages;
		$this->view->province_id    = $province_id;
		$this->view->city_id        = $city_id;
		$this->view->subdistrict_id = $subdistrict_id;
	}

	private function _set_model_attributes(&$user) {
		$village_id = $this->request->getPost('village_id', 'int');
		if ($village_id) {
			$user->village_id = Village::findFirst($village_id)->id;
		}
		if ($user->role->name == 'Merchant') {
			$user->setDeposit($this->request->getPost('deposit'));
		}
		$user->setMinimumPurchase($this->request->getPost('minimum_purchase'));
		$user->setAdminFee($this->request->getPost('admin_fee'));
		$user->setAccumulationDivisor($this->request->getPost('accumulation_divisor'));
		$user->setName($this->request->getPost('name'));
		$user->setEmail($this->request->getPost('email'));
		$user->setNewPassword($this->request->getPost('new_password'));
		$user->setNewPasswordConfirmation($this->request->getPost('new_password_confirmation'));
		$user->setAddress($this->request->getPost('address'));
		$user->setMobilePhone($this->request->getPost('mobile_phone'));
		$user->setCompany($this->request->getPost('company'));
		$user->setGender($this->request->getPost('gender'));
		$user->setDateOfBirth($this->request->getPost('date_of_birth'));
		$user->setNewAvatar($_FILES['new_avatar']);
		$user->setOpenOnSunday($this->request->getPost('open_on_sunday'));
		$user->setOpenOnMonday($this->request->getPost('open_on_monday'));
		$user->setOpenOnTuesday($this->request->getPost('open_on_tuesday'));
		$user->setOpenOnWednesday($this->request->getPost('open_on_wednesday'));
		$user->setOpenOnThursday($this->request->getPost('open_on_thursday'));
		$user->setOpenOnFriday($this->request->getPost('open_on_friday'));
		$user->setOpenOnSaturday($this->request->getPost('open_on_saturday'));
		$user->setBusinessOpeningHour($this->request->getPost('business_opening_hour'));
		$user->setBusinessClosingHour($this->request->getPost('business_closing_hour'));
		$user->setMerchantNote($this->request->getPost('merchant_note'));
		$user->role_id = Role::findFirst(['id > 1 AND id = ?0', 'bind' => [$this->request->getPost('role_id', 'int')]])->id;
		if ($user->role_id == Role::MERCHANT) {
			$user->setDeliveryHours($this->request->getPost('delivery_hours'));
		}
	}
}