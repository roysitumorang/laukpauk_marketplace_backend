<?php

namespace Application\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class PageCategory extends BaseModel {
	public $id;
	public $name;
	public $has_create_page_menu;
	public $has_picture_icon;
	public $has_content;
	public $has_url;
	public $has_link_target;
	public $has_rich_editor;
	public $created_by;
	public $created_at;
	public $updated_by;
	public $updated_at;

	private $_filter;

	function getSource() {
		return 'page_categories';
	}

	function onConstruct() {
		$this->_filter = $this->getDI()->getFilter();
	}

	function initialize() {
		parent::initialize();
		$this->keepSnapshots(true);
		$this->hasMany('id', 'Application\Models\Page', 'page_category_id', [
			'alias'      => 'pages',
			'foreignKey' => [
				'message' => 'kategori tidak dapat dihapus karena memiliki page',
			],
		]);
	}

	function setName(string $name) {
		$this->name = $this->_filter->sanitize($name, ['string', 'trim']);
	}

	function setHasCreatePageMenu($has_create_page_menu) {
		$this->has_create_page_menu = $this->_filter->sanitize($has_create_page_menu, 'int') ?? 0;
	}

	function setHasPictureIcon($has_picture_icon) {
		$this->has_picture_icon = $this->_filter->sanitize($has_picture_icon, 'int') ?? 0;
	}

	function setHasContent($has_content) {
		$this->has_content = $this->_filter->sanitize($has_content, 'int') ?? 0;
	}

	function setHasUrl($has_url) {
		$this->has_url = $this->_filter->sanitize($has_url, 'int') ?? 0;
	}

	function setHasLinkTarget($has_link_target) {
		$this->has_link_target = $this->_filter->sanitize($has_link_target, 'int') ?? 0;
	}

	function setHasRichEditor($has_rich_editor) {
		$this->has_rich_editor = $this->_filter->sanitize($has_rich_editor, 'int') ?? 0;
	}

	function validation() {
		$validator = new Validation;
		$validator->add('name', new PresenceOf([
			'message' => 'nama harus diisi',
		]));
		$validator->add('name', new Uniqueness([
			'convert' => function(array $values) : array {
				$values['name'] = strtolower($values['name']);
				return $values;
			},
			'message' => 'nama sudah ada',
		]));
		return $this->validate($validator);
	}
}