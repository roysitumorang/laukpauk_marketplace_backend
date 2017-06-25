<?php

namespace Application\Api\V3\Merchant;

use Application\Models\UserProduct;
use Ds\Set;
use Phalcon\Db;
use Phalcon\Exception;

class ProductsController extends ControllerBase {
	function indexAction() {
		$category_id  = $this->dispatcher->getParam('category_id', 'int');
		$page         = $this->dispatcher->getParam('page', 'int');
		$search_query = $this->dispatcher->getParam('keyword', 'string') ?: null;
		$limit        = 10;
		$params       = [];
		$products     = [];
		$query        = <<<QUERY
			SELECT
				COUNT(1)
			FROM
				user_product a
				JOIN products b ON a.product_id = b.id
				JOIN product_categories c ON b.product_category_id = c.id
			WHERE
				a.user_id = {$this->_current_user->id} AND
				b.published = 1 AND
				c.published = 1
QUERY;
		if ($search_query) {
			$stop_words            = preg_split('/,/', $this->db->fetchColumn("SELECT value FROM settings WHERE name = 'stop_words'"), -1, PREG_SPLIT_NO_EMPTY);
			$keywords              = preg_split('/ /', strtolower($search_query), -1, PREG_SPLIT_NO_EMPTY);
			$filtered_keywords     = array_diff($keywords, $stop_words);
			$filtered_search_query = implode(' ', $filtered_keywords);
			$query                .= ' AND (b.name ILIKE ? OR c.name ILIKE ?';
			foreach (range(1, 2) as $i) {
				$params[] = "%{$filtered_search_query}%";
			}
			if (count($filtered_keywords) > 1) {
				foreach ($filtered_keywords as $keyword) {
					$query .= ' OR b.name ILIKE ? OR c.name ILIKE ?';
					foreach (range(1, 2) as $i) {
						$params[] = "%{$keyword}%";
					}
				}
			}
			$query .= ')';
		}
		if ($category_id) {
			$query .= " AND c.id = {$category_id}";
		}
		$total_products   = $this->db->fetchColumn($query, $params);
		$total_pages      = ceil($total_products / $limit);
		$current_page     = $page > 0 ? $page : 1;
		$offset           = ($current_page - 1) * $limit;
		$result           = $this->db->query(strtr($query, ['COUNT(1)' => 'a.id, b.name, a.price, a.stock, b.stock_unit, b.picture, a.published']) . " ORDER BY b.name LIMIT {$limit} OFFSET {$offset}", $params);
		$picture_root_url = 'http' . ($this->request->getScheme() === 'https' ? 's' : '') . '://' . $this->request->getHttpHost() . '/assets/image/';
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			if ($row->picture) {
				$row->thumbnail = $picture_root_url . strtr($row->picture, ['.jpg' => '120.jpg']);
				$row->picture   = $picture_root_url . strtr($row->picture, ['.jpg' => '300.jpg']);
			} else {
				unset($row->picture);
			}
			$products[] = $row;
		}
		if (!$total_products) {
			if ($keyword) {
				$this->_response['message'] = 'Produk tidak ditemukan.';
			} else if (!$total_pages) {
				$this->_response['message'] = 'Produk belum ada.';
			}
		} else {
			$this->_response['status'] = 1;
		}
		$this->_response['data']['products'] = $products;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function updateAction($id) {
		try {
			if (!$this->request->isPost() || $this->_current_user->role->name != 'Merchant') {
				throw new Exception('Request tidak valid!');
			}
			$user_product = UserProduct::findFirst(['user_id = ?0 AND id = ?1', 'bind' => [$this->_current_user->id, $id]]);
			if (!$user_product) {
				throw new Exception('Produk tidak ditemukan!');
			}
			$user_product->setPrice($this->_post->price);
			$user_product->setStock($this->_post->stock);
			$user_product->setPublished($this->_post->published);
			if ($user_product->validation() && $user_product->update()) {
				$this->_response['status']  = 1;
				throw new Exception('Update produk berhasil!');
			}
			$errors = new Set;
			foreach ($user_product->getMessages() as $error) {
				$errors->add($error->getMessage());
			}
			throw new Exception($errors->join('<br>'));
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}
}