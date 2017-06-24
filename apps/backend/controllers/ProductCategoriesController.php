<?php

namespace Application\Backend\Controllers;

use Application\Models\ProductCategory;
use Application\Models\Product;
use Application\Models\Role;
use Application\Models\User;
use Exception;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class ProductCategoriesController extends ControllerBase {
	private $_user;

	function beforeExecuteRoute() {
		parent::beforeExecuteRoute();
		if (!($user_id = $this->dispatcher->getParam('user_id', 'int')) ||
			!($this->_user = User::findFirst(['id = ?0 AND role_id = ?1', 'bind' => [$user_id, Role::MERCHANT]]))) {
			$this->flashSession->error('Member tidak ditemukan!');
			$this->response->redirect('/admin/users');
			$this->response->sendHeaders();
		}
	}

	function indexAction() {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$keyword      = $this->dispatcher->getParam('keyword');
		$builder      = $this->modelsManager->createBuilder()
			->columns([
				'a.id',
				'a.user_id',
				'a.name',
				'permalink'      => 'a.permalink',
				'picture'        => 'a.picture',
				'published'      => 'a.published',
				'description'    => 'a.description',
				'meta_title'     => 'a.meta_title',
				'meta_desc'      => 'a.meta_desc',
				'meta_keyword'   => 'a.meta_keyword',
				'created_by'     => 'a.created_by',
				'created_at'     => 'a.created_at',
				'updated_by'     => 'a.updated_by',
				'updated_at'     => 'a.updated_at',
				'total_products' => 'COUNT(DISTINCT b.id)',
			])
			->from(['a' => 'Application\Models\ProductCategory'])
			->leftJoin('Application\Models\Product', "a.id = b.product_category_id AND b.user_id = {$this->_user->id}", 'b')
			->groupBy('a.id')
			->orderBy('a.name ASC');
		$builder->where('a.user_id ' . ($this->_user->premium_merchant ? "= {$this->_user->id}" : 'IS NULL'));
		if ($keyword) {
			$builder->andWhere('a.name ILIKE ?0', ["%{$keyword}%"]);
		}
		$paginator  = new PaginatorQueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page       = $paginator->getPaginate();
		$pages      = $this->_setPaginationRange($page);
		$categories = [];
		foreach ($page->items as $item) {
			$category = ProductCategory::findFirst($item->id);
			$item->writeAttribute('rank', ++$offset);
			$item->writeAttribute('thumbnail', $category->thumbnail(120));
			$categories[] = $item;
		}
		$this->view->keyword    = $keyword;
		$this->view->categories = $categories;
		$this->view->page       = $paginator->getPaginate();
		$this->view->pages      = $pages;
		$this->_prepare_datas();
	}

	function createAction() {
		$category = new ProductCategory;
		if ($this->request->isPost()) {
			$this->_set_model_attributes($category);
			if ($category->validation() && $category->create()) {
				$this->flashSession->success('Penambahan data berhasil.');
				return $this->response->redirect("/admin/users/{$this->_user->id}/product_categories");
			}
			$this->flashSession->error('Penambahan data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($category->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		} else {
			$parent_id = $this->dispatcher->getParam('parent_id', 'int');
			if ($parent_id && ProductCategory::findFirst($parent_id)) {
				$category->parent_id = $parent_id;
			}
		}
		$this->_prepare_datas($category);
	}

	function updateAction($id) {
		if (!filter_var($id, FILTER_VALIDATE_INT) || !($category = ProductCategory::findFirst(['id = ?0 AND user_id ' . ($this->_user->premium_merchant ? "= {$this->_user->id}" : 'IS NULL'), 'bind' => [$id]]))) {
			$this->flashSession->error('Kategori tidak ditemukan.');
			return $this->dispatcher->forward('product_categories');
		}
		$category->thumbnail = $category->thumbnail(120);
		if ($this->request->isPost()) {
			$this->_set_model_attributes($category);
			if ($category->validation() && $category->update()) {
				$this->flashSession->success('Update data berhasil.');
				return $this->response->redirect("/admin/users/{$this->_user->id}/product_categories/{$category->id}/update");
			}
			$this->flashSession->error('Update data tidak berhasil, silahkan cek form dan coba lagi.');
			foreach ($category->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_prepare_datas($category);
	}

	function publishAction($id) {
		if ($this->request->isPost()) {
			$category = ProductCategory::findFirst(['id = ?0 AND user_id ' . ($this->_user->premium_merchant ? "= {$this->_user->id}" : 'IS NULL') . ' AND published = 0', 'bind' => [$id]]);
			if ($category) {
				$category->update(['published' => 1]);
			} else {
				$this->flashSession->error('Kategori tidak ditemukan!');
			}
		}
		return $this->response->redirect($this->request->get('next'));
	}

	function unpublishAction($id) {
		if ($this->request->isPost()) {
			$category = ProductCategory::findFirst(['id = ?0 AND user_id ' . ($this->_user->premium_merchant ? "= {$this->_user->id}" : 'IS NULL') . ' AND published = 1', 'bind' => [$id]]);
			if ($category) {
				$category->update(['published' => 0]);
			} else {
				$this->flashSession->error('Kategori tidak ditemukan!');
			}
		}
		return $this->response->redirect($this->request->get('next'));
	}

	function deletePictureAction($id) {
		if ($this->request->isPost()) {
			$category = ProductCategory::findFirst(['id = ?0 AND picture IS NOT NULL AND user_id ' . ($this->_user->premium_merchant ? "= {$this->_user->id}" : 'IS NULL'), 'bind' => [$id]]);
			if ($category) {
				$category->deletePicture();
				return $this->response->redirect("/admin/users/{$this->_user->id}/product_categories/{$category->id}/update");
			}
			$this->flashSession->error('Kategori tidak ditemukan!');
		}
		return $this->response->redirect('/admin/product_categories');
	}

	function deleteAction($id) {
		try {
			if (!filter_var($id, FILTER_VALIDATE_INT) || !($category = ProductCategory::findFirst(['id = ?0 AND user_id ' . ($this->_user->premium_merchant ? "= {$this->_user->id}" : 'IS NULL'), 'bind' => [$id]]))) {
				throw new Exception('Data tidak ditemukan.');
			}
			if (Product::findFirstByProductCategoryId($category->id)) {
				throw new Exception('Data tidak dapat dihapus karena memilik sub kategori / product');
			}
			$category->delete();
			$this->flashSession->success('Data berhasil dihapus');
		} catch (Exception $e) {
			$this->flashSession->error($e->getMessage());
		} finally {
			return $this->response->redirect("/admin/users/{$this->_user->id}/product_categories");
		}
	}

	private function _prepare_datas(ProductCategory $category = null) {
		$this->view->category = $category;
		$this->view->user     = $this->_user;
		$this->view->menu     = $this->_menu('Members');
	}

	private function _set_model_attributes(&$category) {
		$category->setUserId($this->_user->id);
		$category->setName($this->request->getPost('name'));
		$category->setNewPermalink($this->request->getPost('new_permalink'));
		$category->setPublished($this->request->getPost('published'));
		$category->setDescription($this->request->getPost('description'));
		$category->setMetaTitle($this->request->getPost('meta_title'));
		$category->setMetaDesc($this->request->getPost('meta_desc'));
		$category->setMetaKeyword($this->request->getPost('meta_keyword'));
		$category->setNewPicture($_FILES['picture']);
	}
}