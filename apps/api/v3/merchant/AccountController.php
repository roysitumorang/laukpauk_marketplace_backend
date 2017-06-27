<?php

namespace Application\Api\V3\Merchant;

use Application\Models\Device;
use Application\Models\LoginHistory;
use Application\Models\Role;
use Application\Models\User;
use Application\Models\Village;
use Ds\Set;
use Phalcon\Crypt;
use Phalcon\Db;
use stdClass;

class AccountController extends ControllerBase {
	function beforeExecuteRoute() {
		if ($this->dispatcher->getActionName() != 'authorize') {
			parent::beforeExecuteRoute();
		}
	}

	function updateAction() {
		if ($this->request->isOptions()) {
			$business_hours = new stdClass;
			$provinces      = [];
			$cities         = [];
			$subdistricts   = [];
			$villages       = [];
			foreach (range(User::BUSINESS_HOURS['opening'], User::BUSINESS_HOURS['closing']) as $hour) {
				$business_hours->$hour = ($hour < 10 ? '0' . $hour : $hour) . ':00';
			}
			$result = $this->db->query(<<<QUERY
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
				WHERE e.user_id = {$this->_current_user->id}
				GROUP BY a.id
				ORDER BY a.name
QUERY
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
				WHERE e.user_id = {$this->_current_user->id}
				GROUP BY b.id
				ORDER BY name
QUERY
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
				WHERE e.user_id = {$this->_current_user->id}
				GROUP BY c.id
				ORDER BY c.name
QUERY
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
					e.user_id = {$this->_current_user->id} AND
					c.id = {$this->_current_user->village->subdistrict->id}
				GROUP BY d.id
				ORDER BY d.name
QUERY
			);
			$result->setFetchMode(Db::FETCH_OBJ);
			while ($row = $result->fetch()) {
				$villages[] = $row;
			}
			$this->_response['status']                 = 1;
			$this->_response['data']['business_hours'] = $business_hours;
			$this->_response['data']['provinces']      = $provinces;
			$this->_response['data']['cities']         = $cities;
			$this->_response['data']['subdistricts']   = $subdistricts;
			$this->_response['data']['villages']       = $villages;
			$this->_response['data']['current_user']   = [
				'id'                    => $this->_current_user->id,
				'name'                  => $this->_current_user->name,
				'role'                  => $this->_current_user->role->name,
				'mobile_phone'          => $this->_current_user->mobile_phone,
				'address'               => $this->_current_user->address,
				'village_id'            => $this->_current_user->village->id,
				'subdistrict_id'        => $this->_current_user->village->subdistrict->id,
				'city_id'               => $this->_current_user->village->subdistrict->city->id,
				'province_id'           => $this->_current_user->village->subdistrict->city->province->id,
				'open_on_sunday'        => $this->_current_user->open_on_sunday,
				'open_on_monday'        => $this->_current_user->open_on_monday,
				'open_on_tuesday'       => $this->_current_user->open_on_tuesday,
				'open_on_wednesday'     => $this->_current_user->open_on_wednesday,
				'open_on_thursday'      => $this->_current_user->open_on_thursday,
				'open_on_friday'        => $this->_current_user->open_on_friday,
				'open_on_saturday'      => $this->_current_user->open_on_saturday,
				'business_opening_hour' => strval($this->_current_user->business_opening_hour),
				'business_closing_hour' => strval($this->_current_user->business_closing_hour),
				'merchant_note'         => $this->_current_user->merchant_note,
				'minimum_purchase'      => $this->_current_user->minimum_purchase,
				'delivery_hours'        => array_fill_keys($this->_current_user->delivery_hours ?: range($this->_current_user->business_opening_hour, $this->_current_user->business_closing_hour), 1),
			];
			$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
		$this->_current_user->village = Village::findFirst($this->_post->village_id);
		$this->_current_user->setName($this->_post->name);
		$this->_current_user->setMobilePhone($this->_post->mobile_phone);
		$this->_current_user->setAddress($this->_post->address);
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
		if (!$this->_current_user->validation() || !$this->_current_user->update()) {
			$errors = new Set;
			foreach ($this->_current_user->getMessages() as $error) {
				$errors->add($error->getMessage());
			}
			$this->_response['message'] = $errors->join('<br>');
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
		$this->_response['status']               = 1;
		$this->_response['message']              = 'Update profil berhasil!';
		$this->_response['data']['current_user'] = [
			'id'           => $this->_current_user->id,
			'name'         => $this->_current_user->name,
			'role'         => $this->_current_user->role->name,
			'mobile_phone' => $this->_current_user->mobile_phone,
			'address'      => $this->_current_user->address,
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
			'open_on_sunday'        => $this->_current_user->open_on_sunday,
			'open_on_monday'        => $this->_current_user->open_on_monday,
			'open_on_tuesday'       => $this->_current_user->open_on_tuesday,
			'open_on_wednesday'     => $this->_current_user->open_on_wednesday,
			'open_on_thursday'      => $this->_current_user->open_on_thursday,
			'open_on_friday'        => $this->_current_user->open_on_friday,
			'open_on_saturday'      => $this->_current_user->open_on_saturday,
			'business_opening_hour' => strval($this->_current_user->business_opening_hour),
			'business_closing_hour' => strval($this->_current_user->business_closing_hour),
			'merchant_note'         => $this->_current_user->merchant_note,
			'minimum_purchase'      => $this->_current_user->minimum_purchase,
			'delivery_hours'        => array_fill_keys($this->_current_user->delivery_hours ?: range($this->_current_user->business_opening_hour, $this->_current_user->business_closing_hour), 1),
		];
		$this->response->setJsonContent($this->_response);
		return $this->response;
	}

	function authorizeAction() {
		if (!$this->request->isPost()) {
			$this->_response['message'] = 'Request tidak valid!';
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
		$errors = new Set;
		if (!$this->_post->mobile_phone) {
			$errors->add('nomor HP harus diisi');
		}
		if (!$this->_post->password) {
			$errors->add('password harus diisi');
		}
		if (!$errors->isEmpty()) {
			$this->_response['message'] = $errors->join('<br>');
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
		$user = User::findFirst(['status = 1 AND role_id = ?0 AND mobile_phone = ?1', 'bind' => [Role::MERCHANT, $this->_post->mobile_phone]]);
		if (!$user || !$this->security->checkHash($this->_post->password, $user->password)) {
			$this->_response['message'] = 'Nomor HP dan/atau password salah!';
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
		$this->_response['status'] = 1;
		$this->_response['data']   = [
			'access_token' => strtr($crypt->encryptBase64(json_encode(['api_key' => $user->api_key]), $this->config->encryption_key), [
				'+' => '-',
				'/' => '_',
				'=' => ',',
			]),
			'current_user' => [
				'id'           => $user->id,
				'name'         => $user->name,
				'role'         => $user->role->name,
				'mobile_phone' => $user->mobile_phone,
				'address'      => $user->address,
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
				'open_on_sunday'        => $user->open_on_sunday,
				'open_on_monday'        => $user->open_on_monday,
				'open_on_tuesday'       => $user->open_on_tuesday,
				'open_on_wednesday'     => $user->open_on_wednesday,
				'open_on_thursday'      => $user->open_on_thursday,
				'open_on_friday'        => $user->open_on_friday,
				'open_on_saturday'      => $user->open_on_saturday,
				'business_opening_hour' => strval($user->business_opening_hour),
				'business_closing_hour' => strval($user->business_closing_hour),
				'merchant_note'         => $user->merchant_note,
				'minimum_purchase'      => $user->minimum_purchase,
				'delivery_hours'        => array_fill_keys($user->delivery_hours ?: range($user->business_opening_hour, $user->business_closing_hour), 1),
			],
		];
		$this->response->setJsonContent($this->_response);
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
			WHERE e.user_id = {$this->_current_user->id}
			GROUP BY a.id
			ORDER BY a.name'
QUERY
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
				e.user_id = {$this->_current_user->id}
			GROUP BY b.id
			ORDER BY name
QUERY
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
				e.user_id = {$this->_current_user->id}
			GROUP BY c.id
			ORDER BY c.name
QUERY
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
				e.user_id = {$this->_current_user->id}
			GROUP BY d.id
			ORDER BY d.name
QUERY
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

	function summaryAction() {
		$business_days = [
			$this->_current_user->open_on_monday    ? 'Senin'  : ',',
			$this->_current_user->open_on_tuesday   ? 'Selasa' : ',',
			$this->_current_user->open_on_wednesday ? 'Rabu'   : ',',
			$this->_current_user->open_on_thursday  ? 'Kamis'  : ',',
			$this->_current_user->open_on_friday    ? 'Jumat'  : ',',
			$this->_current_user->open_on_saturday  ? 'Sabtu'  : ',',
			$this->_current_user->open_on_sunday    ? 'Minggu' : ',',
		];
		$business_hours = range($this->_current_user->business_opening_hour, $this->_current_user->business_closing_hour);
		if ($hours = $this->_current_user->delivery_hours) {
			foreach ($business_hours as &$hour) {
				if (!in_array($hour, $hours)) {
					$hour = ',';
				} else {
					$hour .= '.00';
				}
			}
		}
		$delivery_hours                                     = trim(preg_replace(['/\,+/', '/(0)([1-9])/', '/([1-2]?[0-9]\.00)(-[1-2]?[0-9]\.00)+(-[1-2]?[0-9]\.00)/'], [',', '\1-\2', '\1\3'], implode('', $business_hours)), ',');
		$this->_response['status']                          = 1;
		$this->_response['data']['company']                 = $this->_current_user->company;
		$this->_response['data']['address']                 = $this->_current_user->address;
		$this->_response['data']['business_days']           = trim(preg_replace(['/\,+/', '/([a-z])([A-Z])/', '/([A-Za-z]+)(-[A-Za-z]+)+(-[A-Za-z]+)/'], [',', '\1-\2', '\1\3'], implode('', $business_days)), ',') ?: '-';
		$this->_response['data']['business_hours']          = $this->_current_user->business_opening_hour . '.00 - ' . $this->_current_user->business_closing_hour . '.00 WIB';
		$this->_response['data']['delivery_hours']          = $delivery_hours ? $delivery_hours . ' WIB' : '-';
		$this->_response['data']['minimum_purchase']        = $this->_current_user->minimum_purchase;
		$this->_response['data']['shipping_cost']           = $this->_current_user->shipping_cost ?? 0;
		$this->_response['data']['merchant_note']           = $this->_current_user->merchant_note;
		$this->_response['data']['deposit']                 = $this->_current_user->deposit;
		$this->_response['data']['total_products']          = $this->_current_user->countProducts();
		$this->_response['data']['total_new_orders']        = $this->_current_user->totalNewOrders();
		$this->_response['data']['total_new_notifications'] = $this->_current_user->totalNewNotifications();
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}
