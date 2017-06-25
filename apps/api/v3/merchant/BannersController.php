<?php

namespace Application\Api\V3\Merchant;

use Application\Models\BannerCategory;

class BannersController extends ControllerBase {
	function beforeExecuteRoute() {}

	function indexAction() {
		$banners  = [];
		$category = BannerCategory::findFirstByName('Login');
		foreach ($category->banners as $banner) {
			if ($banner->published) {
				$banners[] = $this->request->getScheme() . '://' . $this->request->getHttpHost() . '/assets/image/' . $banner->file_name;
			}
		}
		$this->_response['status']          = 1;
		$this->_response['data']['banners'] = $banners;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}
