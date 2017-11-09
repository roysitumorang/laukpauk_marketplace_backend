<?php

namespace Application\Api\V3\Buyer;

use Application\Models\{Device, LoginHistory, Role, User, Village};
use Ds\Set;
use Phalcon\{Crypt, Db};

class AccountController extends ControllerBase {
	function beforeExecuteRoute() {
		if ($this->dispatcher->getActionName() === 'update') {
			parent::beforeExecuteRoute();
		}
	}

	function createAction() {
		try {
			if (!$this->request->isPost()) {
				throw new \Exception('Request tidak valid!');
			}
			$village_id    = filter_var($this->_post->village_id, FILTER_VALIDATE_INT);
			$user          = new User;
			$user->village = Village::findFirst($village_id);
			$user->setName($this->_post->name);
			$user->setNewPassword($this->_post->new_password);
			$user->setNewPasswordConfirmation($this->_post->new_password);
			$user->setMobilePhone($this->_post->mobile_phone);
			$user->setDeposit(0);
			$user->role_id = Role::BUYER;
			if ($this->_post->device_token && strlen($this->_post->device_token) > 36) {
				$old_owner = User::findFirstByDeviceToken($this->_post->device_token);
				if ($old_owner) {
					$old_owner->update(['device_token' => null]);
				}
				$user->setDeviceToken($this->_post->device_token);
			}
			if (!$user->validation() || !$user->create()) {
				$errors = new Set;
				foreach ($user->getMessages() as $error) {
					if ($error->getField() != 'new_password_confirmation') {
						$errors->add($error->getMessage());
					}
				}
				throw new \Exception($errors->join('<br>'));
			}
			if ($this->_post->device_token && strlen($this->_post->device_token) === 36 && !$user->device_token) {
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
		} catch (\Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}

	function activateAction($activation_token) {
		try {
			if (!$this->request->isPost()) {
				throw new \Exception('Request tidak valid!');
			}
			if (!$user = User::findFirst(['status = 0 AND role_id = ?0 AND activation_token = ?1', 'bind' => [Role::BUYER, $activation_token]])) {
				throw new \Exception('Token aktivasi tidak valid!');
			}
			$user->activate();
			$crypt        = new Crypt;
			$current_user = [
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
			];
			$payload                                 = ['api_key' => $user->api_key];
			$this->_response['status']               = 1;
			$this->_response['message']              = 'Aktivasi account berhasil!';
			$this->_response['data']['access_token'] = strtr($crypt->encryptBase64(json_encode($payload), $this->config->encryption_key), [
				'+' => '-',
				'/' => '_',
				'=' => ',',
			]);
			$this->_response['data']['current_user'] = $current_user;
		} catch (\Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}

	function updateAction() {
		try {
			if ($this->request->isOptions()) {
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
						c.id = {$this->_current_user->village->subdistrict_id}
					GROUP BY d.id
					ORDER BY d.name
QUERY
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
				throw new \Exception;
			}
			$this->_current_user->setName($this->_post->name);
			$this->_current_user->setMobilePhone($this->_post->mobile_phone);
			$this->_current_user->setAddress($this->_post->address);
			$this->_current_user->village = Village::findFirst($this->_post->village_id);
			if (!$this->_current_user->validation() || !$this->_current_user->update()) {
				$errors = new Set;
				foreach ($this->_current_user->getMessages() as $error) {
					$errors->add($error->getMessage());
				}
				throw new \Exception($errors->join('<br>'));
			}
			if ($this->_post->device_token) {
				if (strlen($this->_post->device_token) === 36 && !$this->_current_user->device_token) {
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
				} else if ($this->_post->device_token != $this->_current_user->device_token) {
					$old_owner = User::findFirst(['id != ?0 AND device_token = ?1', 'bind' => [$this->_current_user->id, $this->_post->device_token]]);
					if ($old_owner) {
						$old_owner->update(['device_token' => null]);
					}
					$this->_current_user->update(['device_token' => $this->_post->device_token]);
					$this->_current_user->getDevices()->delete();
				}
			}
			$current_user = [
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
			];
			$this->_response['status']               = 1;
			$this->_response['message']              = 'Update profil berhasil!';
			$this->_response['data']['current_user'] = $current_user;
		} catch (\Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}

	function authorizeAction() {
		try {
			$errors = new Set;
			if (!$this->_post->mobile_phone) {
				$errors->add('nomor HP harus diisi');
			}
			if (!$this->_post->password) {
				$errors->add('password harus diisi');
			}
			if (!$errors->isEmpty()) {
				throw new \Exception($errors->join('<br>'));
			}
			$user = User::findFirst(['status = 1 AND role_id = ?0 AND mobile_phone = ?1', 'bind' => [Role::BUYER, $this->_post->mobile_phone]]);
			if (!$user || !$this->security->checkHash($this->_post->password, $user->password)) {
				throw new \Exception('Nomor HP dan/atau password salah!');
			}
			if ($this->_post->device_token) {
				if (strlen($this->_post->device_token) === 36 && !$user->device_token) {
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
				} else if ($this->_post->device_token != $user->device_token) {
					$old_owner = User::findFirst(['id != ?0 AND device_token = ?1', 'bind' => [$user->id, $this->_post->device_token]]);
					if ($old_owner) {
						$old_owner->update(['device_token' => null]);
					}
					$user->update(['device_token' => $this->_post->device_token]);
					$user->getDevices()->delete();
				}
			}
			$crypt                  = new Crypt;
			$login_history          = new LoginHistory;
			$login_history->user_id = $user->id;
			$login_history->create();
			$current_user = [
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
			];
			$payload                                 = ['api_key' => $user->api_key];
			$this->_response['status']               = 1;
			$this->_response['data']['access_token'] = strtr($crypt->encryptBase64(json_encode($payload), $this->config->encryption_key), [
				'+' => '-',
				'/' => '_',
				'=' => ',',
			]);
			$this->_response['data']['current_user'] = $current_user;
		} catch (\Exception $e) {
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
			GROUP BY a.id
			ORDER BY a.name
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
			WHERE a.id = {$id}
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
			WHERE b.id = {$id}
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
			WHERE c.id = {$id}
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
}
