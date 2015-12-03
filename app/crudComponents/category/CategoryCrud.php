<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 28.3.2015
 * Time: 16:51
 */

/**
 * this is an abstract component.
 *
 * For extending, all methods must be implemented, and
 * 	- controls, modals and add template must contain "{extend $baseTemplateFilename}" at the first line!
 *	- controls template could define special block - {block #extraControls}...{/block}
 *	- modals template could define special block - {block #extraModals}...{/block}
 */

namespace App\CrudComponents\Category;


use App\CrudComponents\BaseCrudComponent;
use App\CrudComponents\BaseCrudControlsComponent;
use ReflectionMethod;

abstract class CategoryCrud extends BaseCrudComponent {

	protected $entityName;

	/** @var array */
	public $onAddSub;

	public function __construct($entityName, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->entityName = $entityName;

		$this->onControlsCreate[] = function(BaseCrudControlsComponent &$controlsComponent){
			$controlsComponent->addTemplateVars(array(
				"entityName" 			=> $this->entityName,
				"baseTemplateFilename"	=>  $this->getBaseControlsTemplateFilename()
			));

			$controlsComponent->addActionAvailable("addSub");
		};

		$this->onAddSub = [];
	}

	protected function attached($presenter) {
		parent::attached($presenter);

		$this->template->categoryAdded = false;
		$this->template->categoryEdited = false;
		$this->template->categoryDeleted = false;
		$this->template->subcategoryAdded = false;

		$this->template->entityName = $this->entityName;
		$this->template->baseTemplateFilename = $this->getBaseModalsTemplateFilename();
	}


	public function createComponentAddButton() {
		$c = parent::createComponentAddButton();
		$c->template->entityName = $this->entityName;
		$c->template->baseTemplateFilename = $this->getBaseAddTemplateFilename();
	}

	protected function getBaseModalsTemplateFilename(){
		return $this->getBaseTemplatePath() . DIRECTORY_SEPARATOR . "modals.latte";
	}

	protected function getBaseControlsTemplateFilename(){
		return $this->getBaseTemplatePath() . DIRECTORY_SEPARATOR . "controls.latte";
	}

	protected function getBaseAddTemplateFilename(){
		return $this->getBaseTemplatePath() . DIRECTORY_SEPARATOR . "add.latte";
	}

	/**
	 * This function returns the path of template storage related to the base component
	 * Ex.: /var/www/.../crudComponents/category/controls.latte, where category component is abstract
	 * It will be used to include the base template by the abstract class (category) children
	 * Children then can define specific blocks and then include the base template
	 * @return string
	 */
	protected final function getBaseTemplatePath(){
		$rm = new ReflectionMethod($this, __FUNCTION__);
		return dirname($rm->getFileName());
	}

	public abstract function createComponentCategoryAddForm($name);
	public abstract function createComponentCategoryEditForm($name);
	public abstract function createComponentCategoryAddSubForm($name);
	public abstract function handleDelete($id);
	public abstract function handleEdit($id);
	public abstract function handleAddSub($id);

}