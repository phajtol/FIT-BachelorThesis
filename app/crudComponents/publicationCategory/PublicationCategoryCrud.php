<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 28.3.2015
 * Time: 17:30
 */

namespace App\CrudComponents\PublicationCategory;


use App\CrudComponents\BaseCrudControlsComponent;
use App\CrudComponents\Category\CategoryCrud;

class PublicationCategoryCrud extends CategoryCrud {

	/**
	 * @var \App\Model\Categories
	 */
	protected $publicationCategoryModel;

	/**
	 * @var \App\Model\CategoriesHasPublication
	 */
	protected $categoriesHasPublicationModel;

	/**
	 * @var \Nette\Security\User
	 */
	protected $loggedUser;

	public function __construct(
		\Nette\Security\User $loggedUser,
		\App\Model\Categories $publicationCategoryModel,
		\App\Model\CategoriesHasPublication $categoriesHasPublicationModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {

		parent::__construct("publication category", $parent, $name);

		$this->categoriesHasPublicationModel = $categoriesHasPublicationModel;
		$this->publicationCategoryModel = $publicationCategoryModel;
		$this->loggedUser = $loggedUser;

		$this->template->publicationsRelatedToCategory = array();

		$this->onControlsCreate[] = function(BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedPublications');
		};
	}

	public function createComponentCategoryAddForm($name) {
            $form = new PublicationCategoryAddForm($this->publicationCategoryModel, $this, $name);
            $form->onError[] = function(){
                    $this->redrawControl('categoryAddForm');
            };
            $form->onSuccess[] = function(PublicationCategoryAddForm $form){
                $formValues = $form->getValuesTransformed();

		$formValues['submitter_id'] = $this->loggedUser->id;
		$formValues['categories_id'] = NULL;

		$record = $this->publicationCategoryModel->insert($formValues);

		if($record) {
			$this->template->categoryAdded = true;

			if ($this->presenter->isAjax()) {
				$form->clearValues();
				$this->redrawControl('categoryAddForm');
			} else $this->redirect('this');

			$this->onAdd($record);
		}
            };
	}

	public function createComponentCategoryEditForm($name) {
            $form = new PublicationCategoryEditForm($this, $name);
            $form->onError[] = function(){
                    $this->redrawControl('categoryEditForm');
            };
            $form->onSuccess[] = function(PublicationCategoryEditForm $form) {
		$formValues = $form->getValuesTransformed();

		$formValues['submitter_id'] = $this->loggedUser->id;

		$this->publicationCategoryModel->update($formValues);
		$record = $this->publicationCategoryModel->find($formValues['id']);

		$this->template->categoryEdited = true;

		if($this->presenter->isAjax()) {
			$this->redrawControl('categoryEditForm');
		} else $this->redirect('this');

		$this->onEdit($record);
            };
	}

	public function createComponentCategoryAddSubForm($name) {
            $form = new PublicationCategoryAddSubForm($this->publicationCategoryModel, $this, $name);
            $form->onError[] = function(){
                    $this->redrawControl('categoryAddSubForm');
            };
            $form->onSuccess[] = function(PublicationCategoryAddSubForm $form) {
		$formValues = $form->getValuesTransformed();

		$formValues['submitter_id'] = $this->loggedUser->id;

		$record = $this->publicationCategoryModel->insert($formValues);

		if($record) {
			$this->template->subcategoryAdded = true;

			if ($this->presenter->isAjax()) {
				$this->redrawControl('categoryAddSubForm');
			} else $this->redirect('this');

			$this->onAddSub($record);
		}
            };
	}

	public function handleDelete($id) {
		$record = $this->publicationCategoryModel->find($id);
		if($record) {

			$record->toArray(); // load the object to be passed to the callback

			$this->publicationCategoryModel->deleteCategoryTreeBranch($id);

			$this->template->categoryDeleted = true;

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deleteCategory');
			}

			$this->onDelete($record);
		}
	}

	public function handleEdit($id) {
		$publicationCategory = $this->publicationCategoryModel->find($id);

		$this["categoryEditForm"]->setDefaults($publicationCategory); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('categoryEditForm');
		}
	}

	public function handleAddSub($id) {
		$publicationCategory = $this->publicationCategoryModel->find($id);

		$this["categoryAddSubForm"]["categories_id"]->setValue($id); // set up parent id

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('categoryAddSubForm');
		}
	}

	public function handleShowRelatedPublications($id){

		$result = array();
		$categoriesBranchIds = $this->publicationCategoryModel->getCategoriesTreeIds($id);

		foreach ($categoriesBranchIds as $row) {
			$categories = $this->categoriesHasPublicationModel->findAllBy(array("categories_id" => $row['id']));
			array_push($result, array('categories' => $row, 'categories_has_publication' => $categories));
		}

		$this->template->publicationsRelatedToCategory = $result;

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('publicationsRelatedToCategory');
		}
	}


}