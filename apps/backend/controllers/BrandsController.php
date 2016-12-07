<?php

namespace Application\Backend\Controllers;

use Application\Models\Brand;
use Exception;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use stdClass;

class BrandsController extends ControllerBase {
	private $_thumb;

	function initialize() {
		parent::initialize();
		$this->_thumb                  = new stdClass;
		$this->_thumb->width           = 120;
		$this->_thumb->height          = 120;
		$this->_thumb->default_picture = null;
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$builder      = $this->modelsManager->createBuilder()
			->columns([
				'id'             => 'a.id',
				'name'           => 'a.name',
				'permalink'      => 'a.permalink',
				'picture'        => 'a.picture',
				'description'    => 'a.description',
				'meta_title'     => 'a.meta_title',
				'meta_desc'      => 'a.meta_desc',
				'meta_keyword'   => 'a.meta_keyword',
				'created_by'     => 'a.created_by',
				'created_at'     => 'a.created_at',
				'updated_by'     => 'a.updated_by',
				'updated_at'     => 'a.updated_at',
				'total_products' => 'COUNT(b.id)',
			])
			->from(['a' => 'Application\Models\Brand'])
			->leftJoin('Application\Models\Product', 'a.id = b.brand_id', 'b')
			->groupBy('a.id')
			->orderBy('name ASC');
		$paginator    = new PaginatorQueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page         = $paginator->getPaginate();
		$pages        = $this->_setPaginationRange($page);
		$brands       = [];
		foreach ($page->items as $item) {
			$brand     = Brand::findFirst($item->id);
			$thumbnail = $brand->getThumbnail($this->_thumb->width, $this->_thumb->height, $this->_thumb->default_picture);
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('thumbnail', $thumbnail);
			$brands[]  = $item;
		}
		$this->view->menu   = $this->_menu('Products');
		$this->view->brands = $brands;
		$this->view->page   = $paginator->getPaginate();
		$this->view->pages  = $pages;
	}

	function showAction($id) {
		if (!filter_var($id, FILTER_VALIDATE_INT) || !($brand = Brand::findFirst($id))) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('brands');
		}
	}

	function createAction() {
		$brand = new Brand;
		if ($this->request->isPost()) {
			$brand->setName($this->request->getPost('name'));
			$brand->setDescription($this->request->getPost('description'));
			$brand->setMetaTitle($this->request->getPost('meta_title'));
			$brand->setMetaDesc($this->request->getPost('meta_desc'));
			$brand->setMetaKeyword($this->request->getPost('meta_keyword'));
			$brand->setNewPicture($_FILES['picture']);
			if ($brand->validation() && $brand->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect('/admin/brands');
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($brand->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->brand = $brand;
		$this->view->menu  = $this->_menu('Products');
	}

	function updateAction($id) {
		if (!filter_var($id, FILTER_VALIDATE_INT) || !($brand = Brand::findFirst($id))) {
			$this->flashSession->error('Data tidak ditemukan.');
			return $this->dispatcher->forward('brands');
		}
		$brand->thumbnail = $brand->getThumbnail($this->_thumb->width, $this->_thumb->height, $this->_thumb->default_picture);
		if ($this->request->isPost()) {
			if ($this->dispatcher->hasParam('delete_picture')) {
				$brand->deletePicture();
				return $this->response->redirect("/admin/brands/update/{$brand->id}");
			}
			$brand->setName($this->request->getPost('name'));
			$brand->setNewPermalink($this->request->getPost('new_permalink'));
			$brand->setDescription($this->request->getPost('description'));
			$brand->setMetaTitle($this->request->getPost('meta_title'));
			$brand->setMetaDesc($this->request->getPost('meta_desc'));
			$brand->setMetaKeyword($this->request->getPost('meta_keyword'));
			$brand->setNewPicture($_FILES['picture']);
			if ($brand->validation() && $brand->save()) {
				$this->flashSession->success('Update data berhasil.');
				return $this->response->redirect("/admin/brands/update/{$brand->id}");
			}
			$this->flashSession->error('Update data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($brand->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->view->brand = $brand;
		$this->view->menu  = $this->_menu('Products');
	}

	function deleteAction($id) {
		try {
			if (!filter_var($id, FILTER_VALIDATE_INT) || !($brand = Brand::findFirst($id))) {
				throw new Exception('Data tidak ditemukan.');
			}
			$brand->delete();
			$this->flashSession->success('Data berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect('/admin/brands');
		}
	}
}