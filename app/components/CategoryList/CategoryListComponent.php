<?php

namespace App\Components\CategoryList;

use App\CrudComponents\Category\CategoryCrud;
use Nette\Application\UI\Control;
use ReflectionMethod;


abstract class CategoryListComponent extends Control {

	/**
	 * @var bool - true if the list should contain checkboxes
	 */
	protected $isSelectable = false;

	/**
	 * @var bool - true if the list should contain category controls
	 */
	protected $hasControls = true;

	/**
	 * @var bool - true if checking an item will result in selecting also all its sub-items. applied only if isSelectable = true
	 */
	protected $hasThreeStates = false;

	/**
	 * @var bool true if drag & drop should be enabled
	 */
	protected $hasDnD = false;

	protected $width = '330';

	protected $height = '400';

	/**
	 * This function has to return array with records (already normalized). The element of arrays must be objects/arrays with defined fields:
	 * 		- id - id of the record
	 * 		- parent_id - id of the parent record
	 * 		- name - name of the record
	 * @return array set of hierarchical records - array(array('id'=>..,'parent_id'=>..,'name'=>...),...)
	 */
	protected abstract function getRecords();

    /**
     * This function implements moving the category in the tree (change parent)
     * @param $categoryId int Id of the category to be moved
     * @param $newParentCategoryId int Id of the new parent category
     * @return int - number of affected rows from the db
     */
	protected abstract function moveCategory(int $categoryId, int $newParentCategoryId): int;

    /**
     * Normalizes record array.
     * @param $row
     * @return array - array('id' => .., 'parent_id' => .., 'name' => ..)
     */
	protected abstract function normalizeRecord($row): array;

	/**
	 * @param $name - string name of the component
	 * @return \App\CrudComponents\Category\CategoryCrud
	 */
	protected abstract function createCrudComponent(string $name);


	protected function getCategoryData(): array
    {
		$records = $this->getRecords();
		$recordsByParentId = array();

		foreach ($records as &$record) {
			if ($record['parent_id']) {
				if (!isset($recordsByParentId[$record['parent_id']])) {
				    $recordsByParentId[$record['parent_id']] = array();
                }
				$recordsByParentId[$record['parent_id']][] = &$record;
			}
		}

		foreach($records as &$record) {
			if (isset($recordsByParentId[$record['id']])) {
			    $record['children'] = $recordsByParentId[$record['id']];
            }
		}

		$outArr = array();

		foreach ($records as &$record) {
			$this->remapNormalized($record);
			if (!$record['parent_id']) {
			    $outArr[] = $record;
            }
			unset($record['parent_id']);
		}

		return $outArr;
	}

	public function render()
    {
		$this->template->categoryTree = array();
		$this->template->categoryData = $this->getCategoryData();

		$this->template->control = $this;
		$this->template->uniqid = $this->getUniqueId();

		$this->template->isSelectable = $this->isSelectable ? true : false;
		$this->template->hasThreeStates = $this->hasThreeStates ? true : false;
		$this->template->hasControls = $this->hasControls ? true : false;
		$this->template->hasDnD = $this->hasDnD ? true : false;

		$this->template->width = is_numeric($this->width) ? $this->width . 'px' : $this->width;
		$this->template->height = is_numeric($this->height) ? $this->height . 'px' : $this->height;


		$this->ensureTemplateVarsExist(array(
			"categoryAdded", "subcategoryAdded", "categoryEdited", "categoryDeleted",
			"categoryId"
		));

		$this->template->setFile(dirname($this->getReflection()->getFileName())  . DIRECTORY_SEPARATOR . 'categoryList.latte');
		$this->template->baseTemplateFilename = $this->getBaseTemplatePath() . DIRECTORY_SEPARATOR . "categoryList.latte";

		$this->template->render();
	}

	public final function createComponentCrud($name): ?CategoryCrud
    {
		if (!$this->hasControls) {
		    return null;
        }

		$c = $this->createCrudComponent($name);

		$c->onAddSub[] = function ($record) {
			$this->template->subcategoryAdded = $this->normalizeRecord($record);
			$this->remapNormalized($this->template->subcategoryAdded);
			$this->redrawControl('subcategoryAdded');
		};

		$c->onAdd[] = function ($record) {
			$this->template->categoryAdded = $this->normalizeRecord($record);
			$this->remapNormalized($this->template->categoryAdded);
			$this->redrawControl('categoryAdded');
		};

		$c->onEdit[] = function ($record) {
			$this->template->categoryEdited = $this->normalizeRecord($record);
			$this->remapNormalized($this->template->categoryEdited);
			$this->redrawControl('categoryEdited');
		};

		$c->onDelete[] = function ($record) {
			$this->template->categoryDeleted = $this->normalizeRecord($record);
			$this->remapNormalized($this->template->categoryDeleted);
			$this->redrawControl('categoryDeleted');
		};

		return $c;
	}

	public function handleItemSelected($id): void
    {
		$this->template->categoryId = $id;
		$this->redrawControl('categoryControls');
	}

	public function handleMoveCategory($id, $newParentId): void
    {
		$result = false;
		if($this->hasDnD) {
			$this->moveCategory($id, $newParentId ? $newParentId : null);
			$result = true;
		}
		$this->getPresenter(true)->sendResponse(new \Nette\Application\Responses\JsonResponse(array('success' => $result)));
	}

	protected final function getBaseTemplatePath(): string
    {
		$rm = new ReflectionMethod($this, __FUNCTION__);
		return dirname($rm->getFileName());
	}

	protected function ensureTemplateVarsExist($vars): void
    {
		foreach($vars as $var) {
		    if (!isset($this->template->$var)) {
		        $this->template->$var = null;
            }
        }
	}

	public function remapNormalized(&$record)
    {
		$record['key'] = strval($record['id']);
		unset($record['id']);

		$record['title'] = $record['name'];
		unset($record['name']);

		return $record;
	}

	/**
	 * @return mixed
	 */
	public function getIsSelectable()
    {
		return $this->isSelectable;
	}

	/**
	 * @param mixed $isSelectable
	 */
	public function setIsSelectable($isSelectable)
    {
		$this->isSelectable = $isSelectable;
	}

	/**
	 * @return mixed
	 */
	public function getHasControls()
    {
		return $this->hasControls;
	}

	/**
	 * @param mixed $hasControls
	 */
	public function setHasControls($hasControls)
    {
		$this->hasControls = $hasControls;
	}

	/**
	 * @return boolean
	 */
	public function isHasThreeStates(): bool
    {
		return $this->hasThreeStates;
	}

	/**
	 * @param boolean $hasThreeStates
	 */
	public function setHasThreeStates(bool $hasThreeStates): void
    {
		$this->hasThreeStates = $hasThreeStates;
	}


	/**
	 * @return string
	 */
	public function getWidth(): string
    {
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
	public function setHeight(string $height): void
    {
		$this->height = $height;
	}

	/**
	 * @return boolean
	 */
	public function isHasDnD(): bool
    {
		return $this->hasDnD;
	}

	/**
	 * @param boolean $hasDnD
	 */
	public function setHasDnD(bool $hasDnD): void
    {
		$this->hasDnD = $hasDnD;
	}


}