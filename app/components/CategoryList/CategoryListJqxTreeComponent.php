<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 28.3.2015
 * Time: 19:23
 */

namespace App\Components\CategoryList;


use Nette\Application\UI\Control;
use ReflectionMethod;

abstract class CategoryListJqxTreeComponent extends Control {

	/**
	 * @var bool true if the list should contain checkboxes
	 */
	protected $isSelectable = false;

	/**
	 * @var bool true if the list should contain category controls
	 */
	protected $hasControls = true;

	/**
	 * @var bool true if checking an item will result in selecting also all its sub-items. applied only if isSelectable = true
	 */
	protected $hasThreeStates = false;

	protected $width = '330';

	protected $height = '400';

	/**
	 * This function has to return array with records. The element of arrays must be objects/arrays with defined fields:
	 * 		- id - id of the record
	 * 		- parent_id - id of the parent record
	 * 		- name - name of the record
	 * @return array set of hierarchical records - array(array('id'=>..,'parent_id'=>..,'name'=>...),...)
	 */
	protected abstract function getRecords();

	/**
	 * This function has to return the normalized record array. It must contain following:
	 * 	- id - id of the record
	 *  - parent_id - id of the parent record
	 *  - name - name of the record
	 * @return array array('id' => .., 'parent_id' => .., 'name' => ..)
	 */
	protected abstract function normalizeRecord($row);

	/**
	 * @param $name string name of the component
	 * @return \App\CrudComponents\Category\CategoryCrud
	 */
	protected abstract function createCrudComponent($name);

	public function render(){
		$this->template->categoryTree = $this->getRecords();

		$this->template->control = $this;
		$this->template->uniqid = $this->getUniqueId();

		$this->template->isSelectable = $this->isSelectable ? true : false;
		$this->template->hasThreeStates = $this->hasThreeStates ? true : false;
		$this->template->hasControls = $this->hasControls ? true : false;

		$this->template->width = is_numeric($this->width) ? $this->width . 'px' : $this->width;
		$this->template->height = is_numeric($this->height) ? $this->height . 'px' : $this->height;


		$this->ensureTemplateVarsExist(array(
			"categoryAdded", "subcategoryAdded", "categoryEdited", "categoryDeleted",
			"categoryId"
		));

		$this->template->setFile(dirname($this->getReflection()->getFileName())  . DIRECTORY_SEPARATOR . 'categoryListJqxTree.latte');
		$this->template->baseTemplateFilename = $this->getBaseTemplatePath() . DIRECTORY_SEPARATOR . "categoryListJqxTree.latte";

		$this->template->render();
	}

	public final function createComponentCrud($name){
		if(!$this->hasControls) return null;

		$c = $this->createCrudComponent($name);
		$c->onAddSub[] = function($record){
			$this->template->subcategoryAdded = $this->normalizeRecord($record);
			$this->redrawControl('subcategoryAdded');
		};
		$c->onAdd[] = function($record){
			$this->template->categoryAdded = $this->normalizeRecord($record);
			$this->redrawControl('categoryAdded');
		};
		$c->onEdit[] = function($record){
			$this->template->categoryEdited = $this->normalizeRecord($record);
			$this->redrawControl('categoryEdited');
		};
		$c->onDelete[] = function($record){
			$this->template->categoryDeleted = $this->normalizeRecord($record);
			$this->redrawControl('categoryDeleted');
		};
		return $c;
	}

	public function handleItemSelected($id) {
		$this->template->categoryId = $id;
		$this->redrawControl('categoryControls');
	}

	protected final function getBaseTemplatePath(){
		$rm = new ReflectionMethod($this, __FUNCTION__);
		return dirname($rm->getFileName());
	}

	protected function ensureTemplateVarsExist($vars) {
		foreach($vars as $var) if(!isset($this->template->$var)) $this->template->$var = null;
	}


	/**
	 * @return mixed
	 */
	public function getIsSelectable() {
		return $this->isSelectable;
	}

	/**
	 * @param mixed $isSelectable
	 */
	public function setIsSelectable($isSelectable) {
		$this->isSelectable = $isSelectable;
	}

	/**
	 * @return mixed
	 */
	public function getHasControls() {
		return $this->hasControls;
	}

	/**
	 * @param mixed $hasControls
	 */
	public function setHasControls($hasControls) {
		$this->hasControls = $hasControls;
	}

	/**
	 * @return boolean
	 */
	public function isHasThreeStates() {
		return $this->hasThreeStates;
	}

	/**
	 * @param boolean $hasThreeStates
	 */
	public function setHasThreeStates($hasThreeStates) {
		$this->hasThreeStates = $hasThreeStates;
	}


	/**
	 * @return string
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * @param string $width
	 */
	public function setWidth($width) {
		$this->width = $width;
	}

	/**
	 * @return string
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * @param string $height
	 */
	public function setHeight($height) {
		$this->height = $height;
	}



}