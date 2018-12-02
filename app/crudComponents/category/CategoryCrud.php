<?php

namespace App\CrudComponents\Category;

use App\Components\StaticContentComponent;
use App\CrudComponents\BaseCrudComponent;
use App\CrudComponents\BaseCrudControlsComponent;
use ReflectionMethod;


/**
 * this is an abstract component.
 *
 * For extending, all methods must be implemented, and
 * 	- controls, modals and add template must contain "{extend $baseTemplateFilename}" at the first line!
 *	- controls template could define special block - {block #extraControls}...{/block}
 *	- modals template could define special block - {block #extraModals}...{/block}
 */
abstract class CategoryCrud extends BaseCrudComponent {

	protected $entityName;

	/** @var array */
	public $onAddSub;


    /**
     * CategoryCrud constructor.
     * @param $entityName
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct($entityName, \Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
        parent::__construct();
        if ($parent) {
            $parent->addComponent($this, $name);
        }

		$this->entityName = $entityName;

		$this->onControlsCreate[] = function (BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addTemplateVars(array(
				"entityName" 			=> $this->entityName,
				"baseTemplateFilename"	=>  $this->getBaseControlsTemplateFilename()
			));

			$controlsComponent->addActionAvailable('addSub');
		};

		$this->onAddSub = [];
	}

    /**
     * @param $presenter
     * @throws \ReflectionException
     */
	protected function attached($presenter): void
    {
		parent::attached($presenter);

		$this->template->categoryAdded = false;
		$this->template->categoryEdited = false;
		$this->template->categoryDeleted = false;
		$this->template->subcategoryAdded = false;

		$this->template->entityName = $this->entityName;
		$this->template->baseTemplateFilename = $this->getBaseModalsTemplateFilename();
	}

    /**
     * @return StaticContentComponent
     * @throws \ReflectionException
     */
	public function createComponentAddButton(): StaticContentComponent
    {
		$c = parent::createComponentAddButton();
		$c->template->entityName = $this->entityName;
		$c->template->baseTemplateFilename = $this->getBaseAddTemplateFilename();
		return $c;
	}

    /**
     * @return string
     * @throws \ReflectionException
     */
	protected function getBaseModalsTemplateFilename(): string
    {
		return $this->getBaseTemplatePath() . DIRECTORY_SEPARATOR . "modals.latte";
	}

    /**
     * @return string
     * @throws \ReflectionException
     */
	protected function getBaseControlsTemplateFilename(): string
    {
		return $this->getBaseTemplatePath() . DIRECTORY_SEPARATOR . "controls.latte";
	}

    /**
     * @return string
     * @throws \ReflectionException
     */
	protected function getBaseAddTemplateFilename(): string
    {
		return $this->getBaseTemplatePath() . DIRECTORY_SEPARATOR . "add.latte";
	}

    /**
     * This function returns the path of template storage related to the base component
     * Ex.: /var/www/.../crudComponents/category/controls.latte, where category component is abstract
     * It will be used to include the base template by the abstract class (category) children
     * Children then can define specific blocks and then include the base template
     * @return string
     * @throws \ReflectionException
     */
	protected final function getBaseTemplatePath(): string
    {
		$rm = new ReflectionMethod($this, __FUNCTION__);
		return dirname($rm->getFileName());
	}

	public abstract function createComponentCategoryAddForm(string $name);

	public abstract function createComponentCategoryEditForm(string $name);

	public abstract function createComponentCategoryAddSubForm(string $name);

	public abstract function handleDelete(int $id): void;

	public abstract function handleEdit(int $id): void;

	public abstract function handleAddSub(int $id): void;

}