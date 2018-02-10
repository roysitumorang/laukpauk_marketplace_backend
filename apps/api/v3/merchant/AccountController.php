<?php

namespace Application\Api\V3\Merchant;

use Application\Models\Device;
use Application\Models\LoginHistory;
use Application\Models\Role;
use Application\Models\User;
use Application\Models\Village;
use Ds\Set;
use Phalcon\Db;
use stdClass;

class AccountController extends ControllerBase {
	function updateAction() {
		try {
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
					WHERE e.user_id = {$this->currentUser->id}
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
					WHERE e.user_id = {$this->currentUser->id}
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
					WHERE e.user_id = {$this->currentUser->id}
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
						e.user_id = {$this->currentUser->id} AND
						c.id = {$this->currentUser->village->subdistrict->id}
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
					'id'                    => $this->currentUser->id,
					'name'                  => $this->currentUser->name,
					'role'                  => $this->currentUser->role->name,
					'mobile_phone'          => $this->currentUser->mobile_phone,
					'address'               => $this->currentUser->address,
					'village_id'            => $this->currentUser->village->id,
					'subdistrict_id'        => $this->currentUser->village->subdistrict->id,
					'city_id'               => $this->currentUser->village->subdistrict->city->id,
					'province_id'           => $this->currentUser->village->subdistrict->city->province->id,
					'open_on_sunday'        => $this->currentUser->open_on_sunday,
					'open_on_monday'        => $this->currentUser->open_on_monday,
					'open_on_tuesday'       => $this->currentUser->open_on_tuesday,
					'open_on_wednesday'     => $this->currentUser->open_on_wednesday,
					'open_on_thursday'      => $this->currentUser->open_on_thursday,
					'open_on_friday'        => $this->currentUser->open_on_friday,
					'open_on_saturday'      => $this->currentUser->open_on_saturday,
					'business_opening_hour' => strval($this->currentUser->business_opening_hour),
					'business_closing_hour' => strval($this->currentUser->business_closing_hour),
					'merchant_note'         => $this->currentUser->merchant_note,
					'minimum_purchase'      => $this->currentUser->minimum_purchase,
					'delivery_hours'        => array_fill_keys($this->currentUser->delivery_hours ?: range($this->currentUser->business_opening_hour, $this->currentUser->business_closing_hour), 1),
				];
				throw new Exception;
			}
			if (($village_id = filter_var($this->post->village_id, FILTER_VALIDATE_INT)) && ($village = Village::findFirst($village_id))) {
				$this->currentUser->village_id     = $village->id;
				$this->currentUser->subdistrict_id = $village->subdistrict_id;
			}
			$this->currentUser->setName($this->post->name);
			$this->currentUser->setMobilePhone($this->post->mobile_phone);
			$this->currentUser->setAddress($this->post->address);
			$this->currentUser->setBusinessOpeningHour($this->post->business_opening_hour);
			$this->currentUser->setBusinessClosingHour($this->post->business_closing_hour);
			$this->currentUser->setOpenOnSunday($this->post->open_on_sunday);
			$this->currentUser->setOpenOnMonday($this->post->open_on_monday);
			$this->currentUser->setOpenOnTuesday($this->post->open_on_tuesday);
			$this->currentUser->setOpenOnWednesday($this->post->open_on_wednesday);
			$this->currentUser->setOpenOnThursday($this->post->open_on_thursday);
			$this->currentUser->setOpenOnFriday($this->post->open_on_friday);
			$this->currentUser->setOpenOnSaturday($this->post->open_on_saturday);
			$this->currentUser->setMinimumPurchase($this->post->minimum_purchase);
			$this->currentUser->setDeliveryHours($this->post->delivery_hours);
			$this->currentUser->setMerchantNote($this->post->merchant_note);
			if (!$this->currentUser->validation() || !$this->currentUser->update()) {
				$errors = new Set;
				foreach ($this->currentUser->getMessages() as $error) {
					$errors->add($error->getMessage());
				}
				throw new Exception($errors->join('<br>'));
			}
			if ($this->post->device_token) {
				if (strlen($this->post->device_token) === 36 && !$this->currentUser->device_token) {
					$device = Device::findFirstByToken($this->post->device_token);
					if (!$device) {
						$device             = new Device;
						$device->user_id    = $this->currentUser->id;
						$device->token      = $this->post->device_token;
						$device->created_by = $this->currentUser->id;
						$device->create();
					} else {
						$device->user_id    = $this->currentUser->id;
						$device->updated_by = $this->currentUser->id;
						$device->update();
					}
				} else if ($this->post->device_token != $this->currentUser->device_token) {
					$old_owner = User::findFirstByDeviceToken($this->post->device_token);
					if ($old_owner) {
						$old_owner->update(['device_token' => null]);
					}
					$this->currentUser->update(['device_token' => $this->post->device_token]);
					$this->currentUser->getDevices()->delete();
				}
			}
			$this->_response['status']               = 1;
			$this->_response['data']['current_user'] = [
				'id'           => $this->currentUser->id,
				'name'         => $this->currentUser->name,
				'role'         => $this->currentUser->role->name,
				'mobile_phone' => $this->currentUser->mobile_phone,
				'address'      => $this->currentUser->address,
				'village'      => [
					'id'   => $this->currentUser->village->id,
					'name' => $this->currentUser->village->name,
				],
				'subdistrict'  => [
					'id'   => $this->currentUser->village->subdistrict->id,
					'name' => $this->currentUser->village->subdistrict->name,
				],
				'city'         => [
					'id'   => $this->currentUser->village->subdistrict->city->id,
					'name' => $this->currentUser->village->subdistrict->city->name,
				],
				'province'     => [
					'id'   => $this->currentUser->village->subdistrict->city->province->id,
					'name' => $this->currentUser->village->subdistrict->city->province->name,
				],
				'open_on_sunday'        => $this->currentUser->open_on_sunday,
				'open_on_monday'        => $this->currentUser->open_on_monday,
				'open_on_tuesday'       => $this->currentUser->open_on_tuesday,
				'open_on_wednesday'     => $this->currentUser->open_on_wednesday,
				'open_on_thursday'      => $this->currentUser->open_on_thursday,
				'open_on_friday'        => $this->currentUser->open_on_friday,
				'open_on_saturday'      => $this->currentUser->open_on_saturday,
				'business_opening_hour' => strval($this->currentUser->business_opening_hour),
				'business_closing_hour' => strval($this->currentUser->business_closing_hour),
				'merchant_note'         => $this->currentUser->merchant_note,
				'minimum_purchase'      => $this->currentUser->minimum_purchase,
				'delivery_hours'        => array_fill_keys($this->currentUser->delivery_hours ?: range($this->currentUser->business_opening_hour, $this->currentUser->business_closing_hour), 1),
			];
			throw new Exception('Update profil berhasil!');
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}

	function authorizeAction() {
		try {
			if (!$this->request->isPost()) {
				throw new Exception('Request tidak valid!');
			}
			$errors = new Set;
			if (!$this->post->mobile_phone) {
				$errors->add('nomor HP harus diisi');
			}
			if (!$this->post->password) {
				$errors->add('password harus diisi');
			}
			if (!$errors->isEmpty()) {
				throw new Exception($errors->join('<br>'));
			}
			$user = User::findFirst(['status = 1 AND role_id = ?0 AND mobile_phone = ?1', 'bind' => [Role::MERCHANT, $this->post->mobile_phone]]);
			if (!$user || !$this->security->checkHash($this->post->password, $user->password)) {
				$this->_response['message'] = 'Nomor HP dan/atau password salah!';
				$this->response->setJsonContent($this->_response);
				return $this->response;
			}
			if ($this->post->device_token) {
				if (strlen($this->post->device_token) === 36 && !$user->device_token) {
					$device = Device::findFirstByToken($this->post->device_token);
					if (!$device) {
						$device             = new Device;
						$device->token      = $this->post->device_token;
						$device->user_id    = $user->id;
						$device->created_by = $user->id;
						$device->create();
					} else if ($device->user_id != $user->id) {
						$device->user_id    = $user->id;
						$device->updated_by = $user->id;
						$device->update();
					}
				} else if ($this->post->device_token != $user->device_token) {
					$old_owner = User::findFirstByDeviceToken($this->post->device_token);
					if ($old_owner) {
						$old_owner->update(['device_token' => null]);
					}
					$user->update(['device_token' => $this->post->device_token]);
					$user->getDevices()->delete();
				}
			}
			$login_history = new LoginHistory;
			$login_history->create(['user_id' => $user->id]);
			$this->_response['status']               = 1;
			$this->_response['data']['access_token'] = $this->jsonWebToken->encode(['api_key' => $user->api_key]);
			$this->_response['data']['current_user'] = [
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
			];
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
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
			WHERE e.user_id = {$this->currentUser->id}
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
				e.user_id = {$this->currentUser->id}
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
				e.user_id = {$this->currentUser->id}
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
				e.user_id = {$this->currentUser->id}
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
			$this->currentUser->open_on_monday    ? 'Senin'  : ',',
			$this->currentUser->open_on_tuesday   ? 'Selasa' : ',',
			$this->currentUser->open_on_wednesday ? 'Rabu'   : ',',
			$this->currentUser->open_on_thursday  ? 'Kamis'  : ',',
			$this->currentUser->open_on_friday    ? 'Jumat'  : ',',
			$this->currentUser->open_on_saturday  ? 'Sabtu'  : ',',
			$this->currentUser->open_on_sunday    ? 'Minggu' : ',',
		];
		$business_hours = range($this->currentUser->business_opening_hour, $this->currentUser->business_closing_hour);
		if ($hours = $this->currentUser->delivery_hours) {
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
		$this->_response['data']['company']                 = $this->currentUser->company;
		$this->_response['data']['address']                 = $this->currentUser->address;
		$this->_response['data']['business_days']           = trim(preg_replace(['/\,+/', '/([a-z])([A-Z])/', '/([A-Za-z]+)(-[A-Za-z]+)+(-[A-Za-z]+)/'], [',', '\1-\2', '\1\3'], implode('', $business_days)), ',') ?: '-';
		$this->_response['data']['business_hours']          = $this->currentUser->business_opening_hour . '.00 - ' . $this->currentUser->business_closing_hour . '.00 WIB';
		$this->_response['data']['delivery_hours']          = $delivery_hours ? $delivery_hours . ' WIB' : '-';
		$this->_response['data']['minimum_purchase']        = $this->currentUser->minimum_purchase;
		$this->_response['data']['merchant_note']           = $this->currentUser->merchant_note;
		$this->_response['data']['deposit']                 = $this->currentUser->deposit;
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}
