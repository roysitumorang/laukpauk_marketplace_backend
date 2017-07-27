<?php

namespace Application\Api\V2\Controllers;

use Phalcon\Db;
use Phalcon\Exception;

class PostsController extends ControllerBase {
	function beforeExecuteRoute() {
		if ($merchant_token = $this->dispatcher->getParam('merchant_token', 'string')) {
			$this->_premium_merchant = User::findFirst(['status = 1 AND role_id = ?0 AND premium_merchant = 1 AND merchant_token = ?1', 'bind' => [Role::MERCHANT, $merchant_token]]);
		}
	}

	function showAction($permalink) {
		try {
			$post = $this->db->fetchOne('SELECT a.name, b.body FROM post_categories a LEFT JOIN posts b ON a.id = b.post_category_id AND b.user_id ' . ($this->_premium_merchant ? "= {$this->_premium_merchant->id}" : 'IS NULL') . ' WHERE a.permalink = ?', Db::FETCH_OBJ, [$permalink]);
			if (!$post) {
				throw new Exception('Konten tidak ditemukan!');
			}
			$this->_response['status']       = 1;
			$this->_response['data']['post'] = [
				'subject' => $post->name,
				'body'    => strtr($post->body, ["\r\n" => '']),
			];
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			return $this->response;
		}
	}
}