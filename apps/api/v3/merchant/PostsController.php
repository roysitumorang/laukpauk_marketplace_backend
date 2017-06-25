<?php

namespace Application\Api\V3\Merchant;

use Application\Models\PostCategory;
use Exception;

class PostsController extends ControllerBase {
	function beforeExecuteRoute() {}

	function showAction($permalink) {
		try {
			$category = PostCategory::findFirst("published = 1 AND name = 'App Info'");
			if (!$category) {
				throw new Exception('Konten tidak ditemukan!');
			}
			$post = $category->getPosts(['published = 1 AND permalink = ?0', 'bind' => [$permalink]])->getFirst();
			if (!$post) {
				throw new Exception('Konten tidak ditemukan!');
			}
			$this->_response['status']       = 1;
			$this->_response['data']['post'] = [
				'subject' => $post->subject,
				'body'    => str_replace("\r\n", '', $post->body),
			];
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		}
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
}