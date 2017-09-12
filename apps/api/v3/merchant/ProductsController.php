<?php

namespace Application\Api\V3\Merchant;

use Application\Models\UserProduct;
use Exception;
use Phalcon\Db;

class ProductsController extends ControllerBase {
	function indexAction() {
		$category_id  = $this->dispatcher->getParam('category_id', 'int');
		$page         = $this->dispatcher->getParam('page', 'int');
		$search_query = $this->dispatcher->getParam('keyword', 'string') ?: null;
		$limit        = 10;
		$params       = [];
		$keywords     = '';
		$products     = [];
		$query        = <<<QUERY
			SELECT
				COUNT(1)
			FROM
				user_product a
				JOIN products b ON a.product_id = b.id
			WHERE
				a.user_id = {$this->_current_user->id} AND
				b.published = 1
QUERY;
		if ($search_query) {
			$stop_words = preg_split('/,/', $this->db->fetchColumn("SELECT value FROM settings WHERE name = 'stop_words'"), -1, PREG_SPLIT_NO_EMPTY);
			$words      = array_values(array_diff(preg_split('/ /', strtolower($search_query), -1, PREG_SPLIT_NO_EMPTY), $stop_words));
			foreach ($words as $i => $word) {
				$keywords .= ($i > 0 ? ' & ' : '') . $word . ':*';
			}
			$keywords && $query .= " AND b.keywords @@ TO_TSQUERY('{$keywords}')";
		}
		if ($category_id) {
			$query .= " AND b.product_category_id = {$category_id}";
		}
		$total_products = $this->db->fetchColumn($query, $params);
		$total_pages    = ceil($total_products / $limit);
		$current_page   = $page > 0 ? $page : 1;
		$offset         = ($current_page - 1) * $limit;
		$result         = $this->db->query(strtr($query, ['COUNT(1)' => <<<QUERY
			a.id,
			b.name,
			a.price,
			a.stock,
			b.stock_unit,
			b.picture,
			a.published,
			TS_RANK(b.keywords, TO_TSQUERY('{$keywords}')) AS relevancy
QUERY
		]) . " ORDER BY relevancy DESC, b.name LIMIT {$limit} OFFSET {$offset}", $params);
		$picture_root_url = 'http' . ($this->request->getScheme() === 'https' ? 's' : '') . '://' . $this->request->getHttpHost() . '/assets/image/';
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			unset($row->relevancy);
			if ($row->picture) {
				$row->thumbnail = $picture_root_url . strtr($row->picture, ['.jpg' => '120.jpg']);
				$row->picture   = $picture_root_url . strtr($row->picture, ['.jpg' => '300.jpg']);
			} else {
				unset($row->picture);
			}
			$products[] = $row;
		}
		if (!$total_products) {
			if ($keywords) {
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

	function saveAction() {
		try {
			if (!$this->request->isPost()) {
				throw new Exception('Request tidak valid!');
			}
			foreach ($this->_post as $item) {
				$user_product = UserProduct::findFirst(['user_id = ?0 AND id = ?1', 'bind' => [$this->_current_user->id, $item->id]]);
				if (!$user_product) {
					continue;
				}
				$user_product->setPrice($item->price);
				$user_product->setStock($item->stock);
				$user_product->setPublished($item->published);
				$user_product->update();
			}
			throw new Exception('Update produk berhasil!');
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}
}