<?php

namespace Application\Library\Paginator\Adapter;

use Phalcon\Paginator\Adapter\AbstractAdapter;
use Phalcon\Paginator\Exception;
use Phalcon\Paginator\Repository;
use Phalcon\Paginator\RepositoryInterface;
use SeekableIterator;
use Countable;

/**
 * Paginates an already-fetched resultset.
 *
 * Phalcon 3's Phalcon\Paginator\Adapter\Model accepted a resultset through
 * the "data" option. Phalcon 4+ replaced that with "model" + "parameters",
 * which cannot express relation queries such as getRelated(). This adapter
 * keeps the Phalcon 3 behaviour:
 *
 *     (new Resultset(['data' => $resultset, 'limit' => 10, 'page' => 2]))->paginate();
 */
class Resultset extends AbstractAdapter {
	function paginate(): RepositoryInterface {
		$items = $this->config['data'] ?? null;
		if (!$items instanceof SeekableIterator || !$items instanceof Countable) {
			throw new Exception('Invalid data for paginator');
		}
		$limit = (int) $this->limitRows;
		if ($limit <= 0) {
			throw new Exception('The start page number is zero or less');
		}
		$page        = max((int) $this->page, 1);
		$total_items = count($items);
		$total_pages = (int) ceil($total_items / $limit);
		$start       = $limit * ($page - 1);
		$page_items  = [];
		if ($total_items > 0) {
			if ($start < $total_items) {
				$items->seek($start);
			} else {
				$items->seek(0);
				$page = 1;
			}
			$i = 1;
			while ($items->valid()) {
				$page_items[] = $items->current();
				if ($i >= $limit) {
					break;
				}
				$i++;
				$items->next();
			}
		}
		return $this->getRepository([
			Repository::PROPERTY_ITEMS         => $page_items,
			Repository::PROPERTY_TOTAL_ITEMS   => $total_items,
			Repository::PROPERTY_LIMIT         => $limit,
			Repository::PROPERTY_FIRST_PAGE    => 1,
			Repository::PROPERTY_PREVIOUS_PAGE => $page > 1 ? $page - 1 : 1,
			Repository::PROPERTY_CURRENT_PAGE  => $page,
			Repository::PROPERTY_NEXT_PAGE     => min($page + 1, $total_pages),
			Repository::PROPERTY_LAST_PAGE     => $total_pages,
		]);
	}
}
