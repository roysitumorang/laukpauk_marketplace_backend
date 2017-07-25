<?php

namespace Application\Api\V3\Merchant;

use Application\Models\Banner;

class BannersController extends ControllerBase {
	function beforeExecuteRoute() {}

	function indexAction() {
		$banners = [];
		foreach (Banner::find(['published = 1 AND user_id ' . ($this->_premium_merchant ? "= {$this->_premium_merchant->id}" : 'IS NULL'), 'columns' => 'file', 'order' => 'id DESC']) as $banner) {
			$banners[] = $this->request->getScheme() . '://' . $this->request->getHttpHost() . '/assets/image/' . $banner->file;
		}
		$this->_response['status']          = 1;
		$this->_response['data']['banners'] = $banners;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}
