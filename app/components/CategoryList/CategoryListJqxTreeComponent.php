<?php

namespace App\Components\CategoryList;

use App\CrudComponents\Category\CategoryCrud;
use Nette\Application\UI\Control;
use ReflectionMethod;


abstract class CategoryListJqxTreeComponent extends Control {

	/** @var bool - true if the list should contain checkboxes */
	protected $isSelectable = false;

	/** @var bool - true if the list should contain category controls */
	protected $hasControls = true;

	/** @var bool - true if checking an item will result in selecting also all its sub-items. applied only if isSelectable = true */
	protected $hasThreeStates = false;

	/** @var string  */
	protected $width = '330';

	/** @var string  */
	protected $height = '400';

	/**
	 * This function has to return array with records. The element of arrays must be objects/arrays with defined fields:
	 * 		- id - id of the record
	 * 		- parent_id - id of the parent record
	 * 		- name - name of the record
	 * @return array set of hierarchical records - array(array('id'=>..,'parent_id'=>..,'name'=>...),...)
	 */
	protected abstract function getRecords(): array;

    /**
     * This function has to return the normalized record array. It must contain following:
     *    - id - id of the record
     *  - parent_id - id of the parent record
     *  - name - name of the record
     * @param $row
     * @return array array('id' => .., 'parent_id' => .., 'name' => ..)
     */
	protected abstract function normalizeRecord($row): array;

	/**
	 * @param string $name - name of the component
	 * @return \App\CrudComponents\Category\CategoryCrud
	 */
	protected abstract function createCrudComponent(string $name): CategoryCrud;

    /**
     *
     */
	public function render(): void
    {
		$this->template->categoryTree = $this->getRecords();

		$this->template->control = $this;
		$this->template->uniqid = $this->getUniqueId();

		$this->template->isSelectable = $this->isSelectable ? true : false;
		$this->template->hasThreeStates = $this->hasThreeStates ? true : false;
		$this->template->hasControls = $this->hasControls ? true : false;

		$this->template->width = is_numeric($this->width) ? $this->width . 'px' : $this->width;
		$this->template->height = is_numeric($this->height) ? $this->height . 'px' : $this->height;


		$this->ensureTemplateVarsExist([
		    "categoryAdded", "subcategoryAdded", "categoryEdited", "categoryDeleted", "categoryId"
		]);

		$this->template->setFile(dirname($this->getReflection()->getFileName())  . DIRECTORY_SEPARATOR . 'categoryListJqxTree.latte');
		$this->template->baseTemplateFilename = $this->getBaseTemplatePath() . DIRECTORY_SEPARATOR . "categoryListJqxTree.latte";

		$this->template->render();
	}

    /**
     * @param string $name
     * @return CategoryCrud|null
     */
	public final function createComponentCrud(string $name): ?CategoryCrud
    {
		if (!$this->hasControls) {
		    return null;
        }

		$c = $this->createCrudComponent($name);

		$c->onAddSub[] = function ($record) {
			$this->template->subcategoryAdded = $this->normalizeRecord($record);
			$this->redrawControl('subcategoryAdded');
		};

		$c->onAdd[] = function ($record) {
			$this->template->categoryAdded = $this->normalizeRecord($record);
			$this->redrawControl('categoryAdded');
		};

		$c->onEdit[] = function ($record) {
			$this->template->categoryEdited = $this->normalizeRecord($record);
			$this->redrawControl('categoryEdited');
		};

		$c->onDelete[] = function ($record) {
			$this->template->categoryDeleted = $this->normalizeRecord($record);
			$this->redrawControl('categoryDeleted');
		};

		return $c;
	}

    /**
     * @param int $id
     */
	public function handleItemSelected(int $id): void
    {
		$this->template->categoryId = $id;
		$this->redrawControl('categoryControls');
	}

    /**
     * @return string
     * @throws \ReflectionException
     */
	protected final function getBaseTemplatePath(): string
    {
		$rm = new ReflectionMethod($this, __FUNCTION__);
		return dirname($rm->getFileName());
	}

    /**
     * @param $vars
     */
	protected function ensureTemplateVarsExist($vars)
    {
		foreach ($vars as $var) {
		    if (!isset($this->template->$var)) {
		        $this->template->$var = null;
            }
        }
	}

    /**
     * @return bool
     */
	public function getIsSelectable(): bool
    {
		return $this->isSelectable;
	}

	/**
	 * @param bool $isSelectable
	 */
	public function setIsSelectable(bool $isSelectable): void
    {
		$this->isSelectable = $isSelectable;
	}

    /**
     * @return bool
     */
	public function getHasControls(): bool
    {
		return $this->hasControls;
	}

	/**
	 * @param bool $hasControls
	 */
	public function setHasControls(bool $hasControls): void
    {
		$this->hasControls = $hasControls;
	}

	/**
	 * @return bool
	 */
	public function isHasThreeStates(): bool
    {
		return $this->hasThreeStates;
	}

	/**
	 * @param bool $hasThreeStates
	 */
	public function setHasThreeStates(bool $hasThreeStates): void
    {
		$this->hasThreeStates = $hasThreeStates;
	}

	/**
	 * @return string
	 */
	public function getWidth(): string {
		return $this->width;
	}

	/**
	 * @param string $width
	 */
	public function setWidth(string $width): void
    {
		$this->width = $width;
	}

	/**
	 * @return string
	 */
	public function getHeight(): string
    {
		return $this->height;
	}

	/**
	 * @param string $height
	 */
	public function setHeight(string $height): void {
		$this->height = $height;
	}

}
