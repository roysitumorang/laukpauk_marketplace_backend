<?php

namespace Application\Api\V3\Buyer;

use Application\Models\Device;
use Application\Models\LoginHistory;
use Application\Models\Role;
use Application\Models\User;
use Application\Models\Village;
use Phalcon\Crypt;
use Phalcon\Db;
use stdClass;

class AccountController extends ControllerBase {
	function beforeExecuteRoute() {
		if ($this->dispatcher->getActionName() === 'update') {
			parent::beforeExecuteRoute();
		}
	}

	function createAction() {
		if (!$this->request->isPost()) {
			$this->_response['message'] = 'Request tidak valid!';
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
		$merchant_token = $this->dispatcher->getParam('merchant_token', 'string');
		if ($merchant_token) {
			$premium_merchant = User::findFirst(['status = 1 AND premium_merchant = 1 AND role_id = ?0 AND merchant_token = ?1', 'bind' => [Role::MERCHANT, $merchant_token]]);
		}
		$village_id    = filter_var($this->_post->village_id, FILTER_VALIDATE_INT);
		$user          = new User;
		$user->village = Village::findFirst($village_id);
		$user->setName($this->_post->name);
		$user->setNewPassword($this->_post->new_password);
		$user->setNewPasswordConfirmation($this->_post->new_password);
		$user->setMobilePhone($this->_post->mobile_phone);
		$user->setDeposit(0);
		$user->role_id = Role::findFirstByName('Buyer')->id;
		if ($premium_merchant) {
			$user->merchant_id = $premium_merchant->id;
		}
		if (!$user->validation() || !$user->create()) {
			$errors = [];
			foreach ($user->getMessages() as $error) {
				if ($error->getField() != 'new_password_confirmation') {
					$errors[] = $error->getMessage();
				}
			}
			$this->_response['message'] = implode('<br>', $errors);
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
		if ($this->_post->device_token) {
			$device = Device::findFirstByToken($this->_post->device_token);
			if (!$device) {
				$device             = new Device;
				$device->user       = $user;
				$device->token      = $this->_post->device_token;
				$device->created_by = $user->id;
				$device->create();
			} else {
				$device->user       = $user;
				$device->updated_by = $user->id;
				$device->update();
			}
		}
		$this->_response['status']                   = 1;
		$this->_response['data']['activation_token'] = $user->activation_token;
		$this->response->setJsonContent($this->_response);
		return $this->response;
	}

	function activateAction($activation_token) {
		if (!$this->request->isPost()) {
			$this->_response['message'] = 'Request tidak valid!';
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
		$merchant_token = $this->dispatcher->getParam('merchant_token', 'string');
		if ($merchant_token) {
			$premium_merchant = User::findFirst(['status = 1 AND premium_merchant = 1 AND role_id = ?0 AND merchant_token = ?1', 'bind' => [Role::MERCHANT, $merchant_token]]);
		}
		$params = $premium_merchant
			? ['status = 0 AND role_id = ?0 AND activation_token = ?1 AND merchant_id = ?2', 'bind' => [Role::BUYER, $activation_token, $premium_merchant->id]]
			: ['status = 0 AND role_id = ?0 AND activation_token = ?1 AND merchant_id IS NULL', 'bind' => [Role::BUYER, $activation_token]];
		if (!($user = User::findFirst($params))) {
			$this->_response['message'] = 'Token aktivasi tidak valid!';
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
		$user->activate();
		$crypt        = new Crypt;
		$current_user = [
			'id'           => $user->id,
			'name'         => $user->name,
			'role'         => $user->role->name,
			'mobile_phone' => $user->mobile_phone,
			'address'      => $user->address,
			'subdistrict'  => [
				'id'   => $user->village->subdistrict->id,
				'name' => $user->village->subdistrict->name,
			],
			'village'      => [
				'id'   => $user->village->id,
				'name' => $user->village->name,
			],
			'subdistrict'  => [
				'id'   => $user->village->subdistrict->id,
				'name' => $user->village->subdistrict->name,
			],
			'city'         => [
				'id'   => $user->village->subdistrict->city->id,
				'name' => $user->village->subdistrict->city->name,
			],
			'province'     => [
				'id'   => $user->village->subdistrict->city->province->id,
				'name' => $user->village->subdistrict->city->province->name,
			],
		];
		$payload = ['api_key' => $user->api_key];
		if ($premium_merchant) {
			$payload['merchant_token'] = $premium_merchant->merchant_token;
		}
		$this->_response['status']  = 1;
		$this->_response['message'] = 'Aktivasi account berhasil!';
		$this->_response['data']    = [
			'access_token' => strtr($crypt->encryptBase64(json_encode($payload), $this->config->encryption_key), [
				'+' => '-',
				'/' => '_',
				'=' => ',',
			]),
			'current_user' => $current_user,
		];
		$this->response->setJsonContent($this->_response);
		return $this->response;
	}

	function updateAction() {
		if ($this->request->isOptions()) {
			if ($this->_current_user->role->name == 'Merchant') {
				$business_hours = new stdClass;
				foreach (range(User::BUSINESS_HOURS['opening'], User::BUSINESS_HOURS['closing']) as $hour) {
					$business_hours->$hour = ($hour < 10 ? '0' . $hour : $hour) . ':00';
				}
				$this->_response['data']['business_hours'] = $business_hours;
				$this->_response['data']['deposit']        = $this->_current_user->deposit;
			}
			$provinces    = [];
			$cities       = [];
			$subdistricts = [];
			$villages     = [];
			$result       = $this->db->query(<<<QUERY
				SELECT
					a.id,
					a.name
				FROM
					provinces a
					JOIN cities b ON a.id = b.province_id
					JOIN subdistricts c ON b.id = c.city_id
					JOIN villages d ON c.id = d.subdistrict_id
					JOIN coverage_area e ON d.id = e.village_id
					JOIN users f ON e.user_id = f.id
				WHERE
QUERY
				. ($this->_premium_merchant ? " e.user_id = {$this->_premium_merchant->id}" : ' f.premium_merchant IS NULL') . ' GROUP BY a.id ORDER BY a.name'
			);
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($row = $result->fetch()) {
				$provinces[] = $row;
			}
			$result = $this->db->query(<<<QUERY
				SELECT
					b.id,
					CONCAT_WS(' ', b.type, b.name) AS name
				FROM
					cities b
					JOIN subdistricts c ON b.id = c.city_id
					JOIN villages d ON c.id = d.subdistrict_id
					JOIN coverage_area e ON d.id = e.village_id
					JOIN users f ON e.user_id = f.id
				WHERE
QUERY
				. ($this->_premium_merchant ? " e.user_id = {$this->_premium_merchant->id}" : ' f.premium_merchant IS NULL') . " GROUP BY b.id ORDER BY CONCAT_WS(' ', b.type, b.name)"
			);
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($row = $result->fetch()) {
				$cities[] = $row;
			}
			$result = $this->db->query(<<<QUERY
				SELECT
					c.id,
					c.name
				FROM
					subdistricts c
					JOIN villages d ON c.id = d.subdistrict_id
					JOIN coverage_area e ON d.id = e.village_id
					JOIN users f ON e.user_id = f.id
				WHERE
QUERY
				. ($this->_premium_merchant ? " e.user_id = {$this->_premium_merchant->id}" : ' f.premium_merchant IS NULL') . ' GROUP BY c.id ORDER BY c.name'
			);
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($row = $result->fetch()) {
				$subdistricts[] = $row;
			}
			$result = $this->db->query(<<<QUERY
				SELECT
					d.id,
					d.name
				FROM
					subdistricts c
					JOIN villages d ON c.id = d.subdistrict_id
					JOIN coverage_area e ON d.id = e.village_id
					JOIN users f ON e.user_id = f.id
				WHERE
QUERY
				. ($this->_premium_merchant ? " e.user_id = {$this->_premium_merchant->id}" : ' f.premium_merchant IS NULL') . " AND c.id = {$this->_current_user->village->subdistrict->id} GROUP BY d.id ORDER BY d.name"
			);
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($row = $result->fetch()) {
				$villages[] = $row;
			}
			$this->_response['status']               = 1;
			$this->_response['data']['provinces']    = $provinces;
			$this->_response['data']['cities']       = $cities;
			$this->_response['data']['subdistricts'] = $subdistricts;
			$this->_response['data']['villages']     = $villages;
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$this->_current_user->setName($this->_post->name);
		$this->_current_user->setMobilePhone($this->_post->mobile_phone);
		$this->_current_user->setAddress($this->_post->address);
		$this->_current_user->village = Village::findFirst($this->_post->village_id);
		if ($this->_current_user->role->name == 'Merchant') {
			$this->_current_user->setBusinessOpeningHour($this->_post->business_opening_hour);
			$this->_current_user->setBusinessClosingHour($this->_post->business_closing_hour);
			$this->_current_user->setOpenOnSunday($this->_post->open_on_sunday);
			$this->_current_user->setOpenOnMonday($this->_post->open_on_monday);
			$this->_current_user->setOpenOnTuesday($this->_post->open_on_tuesday);
			$this->_current_user->setOpenOnWednesday($this->_post->open_on_wednesday);
			$this->_current_user->setOpenOnThursday($this->_post->open_on_thursday);
			$this->_current_user->setOpenOnFriday($this->_post->open_on_friday);
			$this->_current_user->setOpenOnSaturday($this->_post->open_on_saturday);
			$this->_current_user->setMinimumPurchase($this->_post->minimum_purchase);
			$this->_current_user->setDeliveryHours($this->_post->delivery_hours);
			$this->_current_user->setMerchantNote($this->_post->merchant_note);
		}
		if (!$this->_current_user->validation() || !$this->_current_user->update()) {
			$errors = [];
			foreach ($this->_current_user->getMessages() as $error) {
				$errors[] = $error->getMessage();
			}
			$this->_response['message'] = implode('<br>', $errors);
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
		if ($this->_post->device_token) {
			$device = Device::findFirstByToken($this->_post->device_token);
			if (!$device) {
				$device             = new Device;
				$device->user       = $this->_current_user;
				$device->token      = $this->_post->device_token;
				$device->created_by = $this->_current_user->id;
				$device->create();
			} else {
				$device->user       = $this->_current_user;
				$device->updated_by = $this->_current_user->id;
				$device->update();
			}
		}
		$current_user = [
			'id'           => $this->_current_user->id,
			'name'         => $this->_current_user->name,
			'role'         => $this->_current_user->role->name,
			'mobile_phone' => $this->_current_user->mobile_phone,
			'address'      => $this->_current_user->address,
			'subdistrict'  => [
				'id'   => $this->_current_user->village->subdistrict->id,
				'name' => $this->_current_user->village->subdistrict->name,
			],
			'village'      => [
				'id'   => $this->_current_user->village->id,
				'name' => $this->_current_user->village->name,
			],
			'subdistrict'  => [
				'id'   => $this->_current_user->village->subdistrict->id,
				'name' => $this->_current_user->village->subdistrict->name,
			],
			'city'         => [
				'id'   => $this->_current_user->village->subdistrict->city->id,
				'name' => $this->_current_user->village->subdistrict->city->name,
			],
			'province'     => [
				'id'   => $this->_current_user->village->subdistrict->city->province->id,
				'name' => $this->_current_user->village->subdistrict->city->province->name,
			],
		];
		if ($this->_current_user->role->name == 'Merchant') {
			$current_user['open_on_sunday']        = $this->_current_user->open_on_sunday;
			$current_user['open_on_monday']        = $this->_current_user->open_on_monday;
			$current_user['open_on_tuesday']       = $this->_current_user->open_on_tuesday;
			$current_user['open_on_wednesday']     = $this->_current_user->open_on_wednesday;
			$current_user['open_on_thursday']      = $this->_current_user->open_on_thursday;
			$current_user['open_on_friday']        = $this->_current_user->open_on_friday;
			$current_user['open_on_saturday']      = $this->_current_user->open_on_saturday;
			$current_user['business_opening_hour'] = strval($this->_current_user->business_opening_hour);
			$current_user['business_closing_hour'] = strval($this->_current_user->business_closing_hour);
			$current_user['merchant_note']         = $this->_current_user->merchant_note;
			$current_user['minimum_purchase']      = $this->_current_user->minimum_purchase;
			$current_user['delivery_hours']        = $this->_current_user->delivery_hours;
			$current_user['delivery_hours']        = array_fill_keys($this->_current_user->delivery_hours ?: range($this->_current_user->business_opening_hour, $this->_current_user->business_closing_hour), 1);
		}
		$this->_response['status']               = 1;
		$this->_response['message']              = 'Edit profil berhasil!';
		$this->_response['data']['current_user'] = $current_user;
		$this->response->setJsonContent($this->_response);
		return $this->response;
	}

	function authorizeAction() {
		$merchant_token = $this->dispatcher->getParam('merchant_token', 'string');
		if ($merchant_token && !($premium_merchant = User::findFirst(['status = 1 AND premium_merchant = 1 AND role_id = ?0 AND merchant_token = ?1', 'bind' => [Role::MERCHANT, $merchant_token]]))) {
			$this->response->setJsonContent(['message' => 'Merchant token tidak valid, silahkan hubungi Tim LaukPauk.id!']);
			return $this->response;
		}
		$errors = [];
		if (!$this->_post->mobile_phone) {
			$errors['mobile_phone'] = 'nomor HP harus diisi';
		}
		if (!$this->_post->password) {
			$errors['password'] = 'password harus diisi';
		}
		if ($errors) {
			$this->_response['message'] = implode('<br>', $errors);
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		if ($premium_merchant && $premium_merchant->mobile_phone == $this->_post->mobile_phone) {
			$user = $premium_merchant;
		} else {
			$params = $premium_merchant
				? ['status = 1 AND mobile_phone = ?0 AND ((role_id = ?1 AND merchant_id = ?2) OR (role_id = ?3 AND merchant_id = ?4))', 'bind' => [$this->_post->mobile_phone, Role::MERCHANT, $premium_merchant->id, Role::BUYER, $premium_merchant->id]]
				: ['status = 1 AND merchant_token IS NULL AND merchant_id IS NULL AND role_id IN ({role_ids:array}) AND mobile_phone = :mobile_phone:', 'bind' => ['role_ids' => [Role::BUYER, Role::MERCHANT], 'mobile_phone' => $this->_post->mobile_phone]];
			$user = User::findFirst($params);
		}
		if (!$user || !$this->security->checkHash($this->_post->password, $user->password)) {
			$this->_response['message'] = 'Nomor HP dan/atau password salah!';
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		if ($this->_post->device_token) {
			$device = Device::findFirstByToken($this->_post->device_token);
			if (!$device) {
				$device             = new Device;
				$device->user       = $user;
				$device->token      = $this->_post->device_token;
				$device->created_by = $user->id;
				$device->create();
			} else if ($device->user_id != $user->id) {
				$device->user       = $user;
				$device->updated_by = $user->id;
				$device->update();
			}
		}
		$crypt                  = new Crypt;
		$login_history          = new LoginHistory;
		$login_history->user_id = $user->id;
		$login_history->create();
		if ($this->_post->device_token) {
			$device = Device::findFirstByToken($this->_post->device_token);
			if (!$device) {
				$device             = new Device;
				$device->user       = $user;
				$device->token      = $this->_post->device_token;
				$device->created_by = $user->id;
				$device->create();
			} else if ($device->user_id != $user->id) {
				$device->user       = $user;
				$device->updated_by = $user->id;
				$device->update();
			}
		}
		$current_user = [
			'id'           => $user->id,
			'name'         => $user->name,
			'role'         => $user->role->name,
			'mobile_phone' => $user->mobile_phone,
			'address'      => $user->address,
			'subdistrict'  => [
				'id'   => $user->village->subdistrict->id,
				'name' => $user->village->subdistrict->name,
			],
			'village'      => [
				'id'   => $user->village->id,
				'name' => $user->village->name,
			],
			'subdistrict'  => [
				'id'   => $user->village->subdistrict->id,
				'name' => $user->village->subdistrict->name,
			],
			'city'         => [
				'id'   => $user->village->subdistrict->city->id,
				'name' => $user->village->subdistrict->city->name,
			],
			'province'     => [
				'id'   => $user->village->subdistrict->city->province->id,
				'name' => $user->village->subdistrict->city->province->name,
			],
		];
		if ($user->role->name === 'Merchant') {
			$current_user['open_on_sunday']        = $user->open_on_sunday;
			$current_user['open_on_monday']        = $user->open_on_monday;
			$current_user['open_on_tuesday']       = $user->open_on_tuesday;
			$current_user['open_on_wednesday']     = $user->open_on_wednesday;
			$current_user['open_on_thursday']      = $user->open_on_thursday;
			$current_user['open_on_friday']        = $user->open_on_friday;
			$current_user['open_on_saturday']      = $user->open_on_saturday;
			$current_user['business_opening_hour'] = strval($user->business_opening_hour);
			$current_user['business_closing_hour'] = strval($user->business_closing_hour);
			$current_user['merchant_note']         = $user->merchant_note;
			$current_user['minimum_purchase']      = $user->minimum_purchase;
			$current_user['delivery_hours']        = array_fill_keys($user->delivery_hours ?: range($user->business_opening_hour, $user->business_closing_hour), 1);
		}
		$payload = ['api_key' => $user->api_key];
		if ($merchant_token) {
			$payload['merchant_token'] = $merchant_token;
		}
		$this->_response['status'] = 1;
		$this->_response['data']   = [
			'access_token' => strtr($crypt->encryptBase64(json_encode($payload), $this->config->encryption_key), [
				'+' => '-',
				'/' => '_',
				'=' => ',',
			]),
			'current_user' => $current_user,
		];
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function provincesAction() {
		$provinces = [];
		$result    = $this->db->query(<<<QUERY
			SELECT
				a.id,
				a.name
			FROM
				provinces a
				JOIN cities b ON a.id = b.province_id
				JOIN subdistricts c ON b.id = c.city_id
				JOIN villages d ON c.id = d.subdistrict_id
				JOIN coverage_area e ON d.id = e.village_id
				JOIN users f ON e.user_id = f.id
			WHERE
QUERY
			. ($this->_premium_merchant ? " e.user_id = {$this->_premium_merchant->id}" : ' f.premium_merchant IS NULL') . ' GROUP BY a.id ORDER BY a.name'
		);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$provinces[] = $row;
		}
		$this->_response['status']            = 1;
		$this->_response['data']['provinces'] = $provinces;
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function citiesAction($id) {
		$cities = [];
		$result = $this->db->query(<<<QUERY
			SELECT
				b.id,
				CONCAT_WS(' ', b.type, b.name) AS name
			FROM
				provinces a
				JOIN cities b ON a.id = b.province_id
				JOIN subdistricts c ON b.id = c.city_id
				JOIN villages d ON c.id = d.subdistrict_id
				JOIN coverage_area e ON d.id = e.village_id
				JOIN users f ON e.user_id = f.id
			WHERE
				a.id = {$id} AND
QUERY
			. ($this->_premium_merchant ? " e.user_id = {$this->_premium_merchant->id}" : ' f.premium_merchant IS NULL') . " GROUP BY b.id ORDER BY CONCAT_WS(' ', b.type, b.name)"
		);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$cities[] = $row;
		}
		$this->_response['status']         = 1;
		$this->_response['data']['cities'] = $cities;
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function subdistrictsAction($id) {
		$subdistricts = [];
		$result       = $this->db->query(<<<QUERY
			SELECT
				c.id,
				c.name
			FROM
				cities b
				JOIN subdistricts c ON b.id = c.city_id
				JOIN villages d ON c.id = d.subdistrict_id
				JOIN coverage_area e ON d.id = e.village_id
				JOIN users f ON e.user_id = f.id
			WHERE
				b.id = {$id} AND
QUERY
			. ($this->_premium_merchant ? " e.user_id = {$this->_premium_merchant->id}" : ' f.premium_merchant IS NULL') . ' GROUP BY c.id ORDER BY c.name'
		);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$subdistricts[] = $row;
		}
		$this->_response['status']               = 1;
		$this->_response['data']['subdistricts'] = $subdistricts;
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function villagesAction($id) {
		$villages = [];
		$result   = $this->db->query(<<<QUERY
			SELECT
				d.id,
				d.name
			FROM
				subdistricts c
				JOIN villages d ON c.id = d.subdistrict_id
				JOIN coverage_area e ON d.id = e.village_id
				JOIN users f ON e.user_id = f.id
			WHERE
				c.id = {$id} AND
QUERY
			. ($this->_premium_merchant ? " e.user_id = {$this->_premium_merchant->id}" : ' f.premium_merchant IS NULL') . ' GROUP BY d.id ORDER BY d.name'
		);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$villages[] = $row;
		}
		$this->_response['status']           = 1;
		$this->_response['data']['villages'] = $villages;
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}