<?php

namespace Application\Api\V3\Buyer;

use Application\Models\Post;
use Ds\Map;

class PostsController extends ControllerBase {
	function beforeExecuteRoute() {}

	function showAction($permalink) {
		try {
			$permalinks = new Map([
				'hubungi-kami' => 'Hubungi Kami',
			]);
			if (!$permalinks->hasKey($permalink)) {
				throw new \Exception('Konten tidak ditemukan!');
			}
			$subject = $permalinks->get($permalink);
			$this->_response['status']       = 1;
			$this->_response['data']['post'] = [
				'subject' => $subject,
				'body'    => strtr(Post::findFirstBySubject($subject)->body, ["\r\n" => '']),
			];
		} catch (\Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
	}
}