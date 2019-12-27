<?php

namespace Application\Backend\Controllers;

use Application\Models\Setting;
use Phalcon\Paginator\Adapter\QueryBuilder;

class SettingsController extends ControllerBase {
	function indexAction() {
		$this->_render();
	}

	function createAction() {
		$setting = new Setting;
		if ($this->request->isPost()) {
			$setting->setName($this->request->getPost('name'));
			$setting->setValue($this->request->getPost('value'));
			if ($setting->validation() && $setting->create()) {
				$page = $this->dispatcher->getParam('page', 'int') ?: 1;
				$this->flashSession->success('Penambahan setting berhasil!');
				return $this->response->redirect('/admin/settings' . ($page > 1 ? '/index/page:' . $page : ''));
			}
			foreach ($setting->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_render($setting);
	}

	function updateAction($id) {
		$setting = Setting::findFirstById($id);
		$page    = $this->dispatcher->getParam('page', 'int') ?: 1;
		if (!$setting) {
			$this->flashSession->error('Setting tidak ditemukan!');
			return $this->response->redirect('/admin/provinces' . ($page > 1 ? '/index/page:' . $page : ''));
		}
		if ($this->request->isPost()) {
			$setting->setValue($this->request->getPost('value'));
			if ($setting->validation() && $setting->update()) {
				$this->flashSession->success('Update setting berhasil!');
				return $this->response->redirect('/admin/settings' . ($page > 1 ? '/index/page:' . $page : ''));
			}
			foreach ($setting->getMessages() as $error) {
				$this->flashSession->error($error);
			}
		}
		$this->_render($setting);
	}

	private function _render(Setting $setting = null) {
		$limit        = $this->config->per_page;
		$current_page = $this->dispatcher->getParam('page', 'int') ?: 1;
		$offset       = ($current_page - 1) * $limit;
		$builder      = $this->modelsManager->createBuilder()
				->from(Setting::class)
				->orderBy('name');
		$paginator    = new QueryBuilder([
			'builder' => $builder,
			'limit'   => $limit,
			'page'    => $current_page,
		]);
		$page     = $paginator->paginate();
		$pages    = $this->_setPaginationRange($page);
		$settings = [];
		foreach ($page->items as $item) {
			$item->writeAttribute('rank', ++$offset);
			$settings[] = $item;
		}
		$this->view->menu     = $this->_menu('Options');
		$this->view->settings = $settings;
		$this->view->page     = $page;
		$this->view->pages    = $pages;
		if ($setting) {
			$this->view->setting = $setting;
		}
	}
}
