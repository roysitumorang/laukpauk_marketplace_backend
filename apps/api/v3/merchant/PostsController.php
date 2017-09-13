<?php

namespace Application\Api\V3\Merchant;

use Application\Models\Post;
use Exception;

class PostsController extends ControllerBase {
	function showAction($permalink) {
		try {
			$post = Post::findFirstByPermalink($permalink);
			if (!$post) {
				throw new Exception('Konten tidak ditemukan!');
			}
			$this->_response['status'] = 1;
			$this->_response['data']   = [
				'post' => ['subject' => $post->subject, 'body' => strtr($post->body, ["\r\n" => ''])],
			];
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response, JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
	}
}