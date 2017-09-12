<?php

namespace Application\Api\V3\Merchant;

use Application\Models\CoverageArea;
use Exception;
use Phalcon\Db;

class ShippingCostsController extends ControllerBase {
	function indexAction() {
		$page           = $this->dispatcher->getParam('page', 'int');
		$search_query   = $this->dispatcher->getParam('keyword', 'string') ?: null;
		$limit          = 10;
		$keywords       = [];
		$shipping_costs = [];
		$params         = [$this->_current_user->id];
		$query          = <<<QUERY
			SELECT
				COUNT(1)
			FROM
				coverage_area a
				JOIN villages b ON a.village_id = b.id
			WHERE
				a.user_id = ?
QUERY;
		if ($search_query) {
			$keywords = preg_split('/ /', strtolower($search_query), -1, PREG_SPLIT_NO_EMPTY);
			foreach ($keywords as $keyword) {
				$query   .= ' AND b.name ILIKE ?';
				$params[] = "%{$keyword}%";
			}
		}
		$total_shipping_costs = $this->db->fetchColumn($query, $params);
		$total_pages          = ceil($total_shipping_costs / $limit);
		$current_page         = $page > 0 ? $page : 1;
		$offset               = ($current_page - 1) * $limit;
		$result               = $this->db->query(strtr($query, ['COUNT(1)' => <<<QUERY
			a.id,
			b.name,
			a.shipping_cost
QUERY
		]) . " ORDER BY b.name LIMIT {$limit} OFFSET {$offset}", $params);
		$result->setFetchMode(Db::FETCH_OBJ);
		while ($row = $result->fetch()) {
			$shipping_costs[] = $row;
		}
		if (!$total_shipping_costs) {
			if ($keywords) {
				$this->_response['message'] = 'Data tidak ditemukan.';
			} else if (!$total_pages) {
				$this->_response['message'] = 'Data belum ada.';
			}
		} else {
			$this->_response['status'] = 1;
		}
		$this->_response['data']['shipping_costs'] = $shipping_costs;
		$this->response->setJsonContent($this->_response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}

	function saveAction() {
		try {
			if (!$this->request->isPost()) {
				throw new Exception('Request tidak valid!');
			}
			foreach ($this->post as $item) {
				$coverage_area = CoverageArea::findFirst(['user_id = ?0 AND id = ?1', 'bind' => [$this->currentUser->id, $item->id]]);
				if (!$coverage_area) {
					continue;
				}
				$coverage_area->setShippingCost($item->shipping_cost);
				$coverage_area->update();
			}
			throw new Exception('Update ongkos kirim berhasil!');
		} catch (Exception $e) {
			$this->_response['message'] = $e->getMessage();
		} finally {
			$this->response->setJsonContent($this->_response);
			return $this->response;
		}
	}
}