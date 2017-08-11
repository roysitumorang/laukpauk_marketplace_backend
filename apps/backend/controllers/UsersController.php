<?php

namespace Application\Backend\Controllers;

use Application\Models\LoginHistory;
use Application\Models\Role;
use Application\Models\User;
use Application\Models\Village;
use Phalcon\Db;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

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
		$current_role             = $this->dispatcher->getParam('role_id', 'int');
		$current_premium_merchant = $this->dispatcher->getParam('merchant_id', 'int');
		$keyword                  = str_replace([':', '/'], '', $this->dispatcher->getParam('keyword', 'string'));
		$users                    = [];
		$premium_merchants        = [];
		$builder                  = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'a.api_key',
				'a.premium_merchant',
				'a.merchant_token',
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
				'a.company_profile',
				'a.terms_conditions',
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
				'merchant'                 => 'h.company',
			])
			->from(['a' => 'Application\Models\User'])
			->join('Application\Models\Role', 'a.role_id = b.id', 'b')
			->leftJoin('Application\Models\Village', 'a.village_id = c.id', 'c')
			->leftJoin('Application\Models\Subdistrict', 'c.subdistrict_id = d.id', 'd')
			->leftJoin('Application\Models\City', 'd.city_id = e.id', 'e')
			->leftJoin('Application\Models\Province', 'e.province_id = f.id', 'f')
			->leftJoin('Application\Models\LoginHistory', 'a.id = g.user_id', 'g')
			->leftJoin('Application\Models\User', 'a.merchant_id = h.id', 'h')
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
		$builder->andWhere('a.merchant_id ' . ($current_premium_merchant ? "= {$current_premium_merchant}" : 'IS NULL'));
		$paginator = new PaginatorQueryBuilder([
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
		foreach (User::find(['role_id = ?0 AND premium_merchant = 1 AND status = 1', 'bind' => [Role::MERCHANT], 'columns' => 'id, company', 'order' => 'company']) as $merchant) {
			$premium_merchants[] = $merchant;
		}
		$this->view->menu                     = $this->_menu('Members');
		$this->view->users                    = $users;
		$this->view->premium_merchants        = $premium_merchants;
		$this->view->pages                    = $pages;
		$this->view->page                     = $paginator->getPaginate();
		$this->view->status                   = $status;
		$this->view->current_status           = $current_status;
		$this->view->roles                    = Role::find(['id > 1', 'order' => 'name']);
		$this->view->current_role             = $current_role;
		$this->view->current_premium_merchant = $current_premium_merchant;
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
		foreach (User::find(['premium_merchant IS NULL AND merchant_id IS NULL AND status = 1', 'columns' => 'name, mobile_phone', 'order' => 'name']) as $user) {
			fputs($output, sprintf('"%s","","%s"' . "\r\n", strtr($user->name, '"', '\"'), preg_replace('/[^\d]+/', '', $user->mobile_phone)));
		}
		fclose($output);
		$this->response->send();
		exit;
	}

	private function _prepare_form_datas(User $user) {
		$provinces      = [];
		$cities         = [];
		$subdistricts   = [];
		$villages       = [];
		$business_hours = [];
		foreach (range(User::BUSINESS_HOURS['opening'], User::BUSINESS_HOURS['closing']) as $hour) {
			$business_hours[$hour] = ($hour < 10 ? '0' . $hour : $hour) . ':00';
		}
		$this->view->menu  = $this->_menu('Members');
		$this->view->roles = Role::find([
			'id IN ({ids:array})',
			'bind'  => ['ids' => [Role::ADMIN, Role::MERCHANT, Role::BUYER]],
			'order' => 'name',
		]);
		$result = $this->db->query(<<<QUERY
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
		$current_province_id              = $user->village->subdistrict->city->province->id ?? array_keys($provinces)[0];
		$current_cities                   = $cities[$current_province_id];
		$current_city_id                  = $user->village->subdistrict->city->id ?? array_keys($current_cities)[0];
		$current_subdistricts             = $subdistricts[$current_city_id];
		$current_subdistrict_id           = $user->village->subdistrict->id ?? array_keys($current_subdistricts)[0];
		$current_villages                 = $villages[$current_subdistrict_id];
		$this->view->user                 = $user;
		$this->view->status               = User::STATUS;
		$this->view->genders              = User::GENDERS;
		$this->view->business_hours       = $business_hours;
		$this->view->provinces            = $provinces;
		$this->view->cities               = $cities;
		$this->view->subdistricts         = $subdistricts;
		$this->view->villages             = $villages;
		$this->view->current_cities       = $current_cities;
		$this->view->current_subdistricts = $current_subdistricts;
		$this->view->current_villages     = $current_villages;
	}

	private function _set_model_attributes(&$user) {
		$village_id = $this->request->getPost('village_id', 'int');
		if ($village_id) {
			$user->village = Village::findFirst($village_id);
		}
		if ($user->role->name == 'Merchant') {
			$user->setDeposit($this->request->getPost('deposit'));
		}
		$user->setPremiumMerchant($this->request->getPost('premium_merchant'));
		$user->setOnesignalAppId($this->request->getPost('onesignal_app_id'));
		$user->setOnesignalApiKey($this->request->getPost('onesignal_api_key'));
		$user->setDomain($this->request->getPost('domain'));
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
		$user->setCompanyProfile($this->request->getPost('company_profile'));
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
		$user->setTermsConditions($this->request->getPost('terms_conditions'));
		$user->setMerchantNote($this->request->getPost('merchant_note'));
		$user->role_id = Role::findFirst(['id > 1 AND id = ?0', 'bind' => [$this->request->getPost('role_id', 'int')]])->id;
		if ($user->role_id == Role::MERCHANT) {
			$user->setDeliveryHours($this->request->getPost('delivery_hours'));
		}
	}
}