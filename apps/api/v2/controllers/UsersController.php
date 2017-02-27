<?php

namespace Application\Api\V2\Controllers;

use Application\Models\City;
use Application\Models\Device;
use Application\Models\Role;
use Application\Models\User;
use Application\Models\Village;
use Phalcon\Crypt;
use Phalcon\Db;
use stdClass;

class UsersController extends ControllerBase {
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
		$village_id    = filter_var($this->_input->village_id, FILTER_VALIDATE_INT);
		$user          = new User;
		$user->village = Village::findFirst($village_id);
		$user->setName($this->_input->name);
		$user->setNewPassword($this->_input->new_password);
		$user->setNewPasswordConfirmation($this->_input->new_password);
		$user->setMobilePhone($this->_input->mobile_phone);
		$user->setDeposit(0);
		$user->role_id = Role::findFirstByName('Buyer')->id;
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
		if ($this->_input->device_token) {
			$device = Device::findFirstByToken($this->_input->device_token);
			if (!$device) {
				$device             = new Device;
				$device->user       = $user;
				$device->token      = $this->_input->device_token;
				$device->created_by = $user->id;
				$device->create();
			} else {
				$device->user       = $user;
				$device->updated_by = $user->id;
				$device->update();
			}
		}
		$this->_response = [
			'status' => 1,
			'data'   => ['activation_token' => $user->activation_token],
		];
		$this->response->setJsonContent($this->_response);
		return $this->response;
	}

	function activateAction($activation_token) {
		if (!$this->request->isPost()) {
			$this->_response['message'] = 'Request tidak valid!';
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
		$user = User::findFirstByActivationToken($activation_token);
		if (!$user) {
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
		$this->_response = [
			'status'  => 1,
			'message' => 'Aktivasi account berhasil!',
			'data'    => [
				'access_token' => strtr($crypt->encryptBase64($user->api_key, $this->config->encryption_key), [
					'+' => '-',
					'/' => '_',
					'=' => ',',
				]),
				'current_user' => $current_user,
			],
		];
		$this->response->setJsonContent($this->_response);
		return $this->response;
	}

	function updateAction() {
		if ($this->request->isGet()) {
			if ($this->_current_user->role->name == 'Merchant') {
				$business_hours = new stdClass;
				foreach (range(User::BUSINESS_HOURS['opening'], User::BUSINESS_HOURS['closing']) as $hour) {
					$business_hours->$hour = ($hour < 10 ? '0' . $hour : $hour) . ':00';
				}
				$this->_response['data']['business_hours'] = $business_hours;
			}
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
			if (!$this->cache->exists('provinces')) {
				$provinces = [];
				$result    = $this->db->query("SELECT a.id AS province_id, a.name AS province_name, b.id AS city_id, CONCAT_WS(' ', b.type, b.name) AS city_name, c.id AS subdistrict_id, c.name AS subdistrict_name, d.id AS village_id, d.name AS village_name FROM provinces a JOIN cities b ON a.id = b.province_id JOIN subdistricts c ON b.id = c.city_id JOIN villages d ON c.id = d.subdistrict_id ORDER BY province_name, city_name, subdistrict_name, village_name");
				$result->setFetchMode(Db::FETCH_OBJ);
				while ($row = $result->fetch()) {
					if (!isset($provinces[$row->province_id])) {
						$provinces[$row->province_id] = [
							'name'   => $row->province_name,
							'cities' => [],
						];
					}
					if (!isset($provinces[$row->province_id]['cities'][$row->city_id])) {
						$provinces[$row->province_id]['cities'][$row->city_id] = [
							'name'         => $row->city_name,
							'subdistricts' => [],
						];
					}
					if (!isset($provinces[$row->province_id]['cities'][$row->city_id]['subdistricts'][$row->subdistrict_id])) {
						$provinces[$row->province_id]['cities'][$row->city_id]['subdistricts'][$row->subdistrict_id] = [
							'name'     => $row->subdistrict_name,
							'villages' => [],
						];
					}
					if (!isset($provinces[$row->province_id]['cities'][$row->city_id]['subdistricts'][$row->subdistrict_id]['villages'][$row->village_id])) {
						$provinces[$row->province_id]['cities'][$row->city_id]['subdistricts'][$row->subdistrict_id]['villages'][$row->village_id] = $row->village_name;
					}
				}
				$this->cache->save('provinces', $provinces);
			}
			$this->_response['status']               = 1;
			$this->_response['data']['subdistricts'] = $this->cache->get('subdistricts');
			$this->_response['data']['provinces']    = $this->cache->get('provinces');
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
		$this->_current_user->setName($this->_input->name);
		$this->_current_user->setMobilePhone($this->_input->mobile_phone);
		$this->_current_user->setAddress($this->_input->address);
		$this->_current_user->village = Village::findFirst($this->_input->village_id);
		if ($this->_current_user->role->name == 'Merchant') {
			$this->_current_user->setBusinessOpeningHour($this->_input->business_opening_hour);
			$this->_current_user->setBusinessClosingHour($this->_input->business_closing_hour);
			$this->_current_user->setOpenOnSunday($this->_input->open_on_sunday);
			$this->_current_user->setOpenOnMonday($this->_input->open_on_monday);
			$this->_current_user->setOpenOnTuesday($this->_input->open_on_tuesday);
			$this->_current_user->setOpenOnWednesday($this->_input->open_on_wednesday);
			$this->_current_user->setOpenOnThursday($this->_input->open_on_thursday);
			$this->_current_user->setOpenOnFriday($this->_input->open_on_friday);
			$this->_current_user->setOpenOnSaturday($this->_input->open_on_saturday);
			$this->_current_user->setMinimumPurchase($this->_input->minimum_purchase);
			$this->_current_user->setDeliveryHours($this->_input->delivery_hours);
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
			$current_user['business_opening_hour'] = $this->_current_user->business_opening_hour;
			$current_user['business_closing_hour'] = $this->_current_user->business_closing_hour;
			$current_user['minimum_purchase']      = $this->_current_user->minimum_purchase;
			$current_user['delivery_hours']        = $this->_current_user->delivery_hours;
			$current_user['delivery_hours']        = array_fill_keys($this->_current_user->delivery_hours ?: range($this->_current_user->business_opening_hour, $this->_current_user->business_closing_hour), 1);
		}
		$this->_response = [
			'status'  => 1,
			'message' => 'Update profile berhasil!',
			'data'    => ['current_user' => $current_user],
		];
		$this->response->setJsonContent($this->_response);
		return $this->response;
	}
}
