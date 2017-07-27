<?php

namespace Application\Api\V3\Buyer;

use Application\Models\Banner;
use Application\Models\Role;
use Application\Models\User;

class BannersController extends ControllerBase {
	function beforeExecuteRoute() {
		if ($merchant_token = $this->dispatcher->getParam('merchant_token', 'string')) {
			$this->_premium_merchant = User::findFirst(['status = 1 AND role_id = ?0 AND premium_merchant = 1 AND merchant_token = ?1', 'bind' => [Role::MERCHANT, $merchant_token]]);
		}
	}

	function indexAction() {
		$banners = [];
		foreach (Banner::find(['published = 1 AND user_id ' . ($this->_premium_merchant ? "= {$this->_premium_merchant->id}" : 'IS NULL'), 'columns' => 'file', 'order' => 'id DESC']) as $banner) {
			$banners[] = $this->request->getScheme() . '://' . $this->request->getHttpHost() . '/assets/image/' . $banner->file;
		}
		$this->_response['status']          = 1;
		$this->_response['data']['banners'] = $banners;
		$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}
