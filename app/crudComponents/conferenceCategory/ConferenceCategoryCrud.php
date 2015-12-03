<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 2.4.2015
 * Time: 22:08
 */

namespace App\CrudComponents\ConferenceCategory;


use App\CrudComponents\BaseCrudComponent;
use App\CrudComponents\BaseCrudControlsComponent;

class ConferenceCategoryCrud extends \App\CrudComponents\Category\CategoryCrud {


	/**
	 * @var \App\Model\ConferenceCategory
	 */
	protected $conferenceCategoryModel;

	/**
	 * @var \App\Model\ConferenceHasCategory
	 */
	protected $conferenceHasCategoryModel;

	/**
	 * @var \Nette\Security\User
	 */
	protected $loggedUser;



	public function __construct(
		\Nette\Security\User $loggedUser,
		\App\Model\ConferenceCategory $conferenceCategoryModel,
		\App\Model\ConferenceHasCategory $conferenceHasCategoryModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {

		parent::__construct("Conference category", $parent, $name);

		$this->conferenceHasCategoryModel = $conferenceHasCategoryModel;
		$this->conferenceCategoryModel = $conferenceCategoryModel;

		$this->onControlsCreate[] = function(BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedConferences');
		};
	}

	protected function attached($presenter) {
		parent::attached($presenter);
		$this->template->conferencesRelatedToCategory = array();
	}


	public function createComponentCategoryAddForm($name) {
		$form = new ConferenceCategoryAddForm($this->conferenceCategoryModel, $this, $name);
		$form->onError[] = function(){
			$this->redrawControl('categoryAddForm');
		};
		$form->onSuccess[] = $this->categoryAddFormSucceeded;
	}

	public function createComponentCategoryEditForm($name) {
		$form = new ConferenceCategoryEditForm($this, $name);
		$form->onError[] = function(){
			$this->redrawControl('categoryEditForm');
		};
		$form->onSuccess[] = $this->categoryEditFormSucceeded;
	}

	public function createComponentCategoryAddSubForm($name) {
		$form = new ConferenceCategoryAddSubForm($this->conferenceCategoryModel, $this, $name);
		$form->onError[] = function(){
			$this->redrawControl('categoryAddSubForm');
		};
		$form->onSuccess[] = $this->categoryAddSubFormSucceeded;
	}

	public function categoryAddFormSucceeded(ConferenceCategoryAddForm $form){
		$formValues = $form->getValuesTransformed();

		$formValues['parent_id'] = NULL;

		$record = $this->conferenceCategoryModel->insert($formValues);

		if($record) {
			$this->template->categoryAdded = true;

			if ($this->presenter->isAjax()) {
				$form->clearValues();
				$this->redrawControl('categoryAddForm');
			} else $this->redirect('this');

			$this->onAdd($record);
		}
	}

	public function categoryEditFormSucceeded(ConferenceCategoryEditForm $form) {
		$formValues = $form->getValuesTransformed();


		$this->conferenceCategoryModel->update($formValues);
		$record = $this->conferenceCategoryModel->find($formValues['id']);

		$this->template->categoryEdited = true;

		if($this->presenter->isAjax()) {
			$this->redrawControl('categoryEditForm');
		} else $this->redirect('this');

		$this->onEdit($record);
	}

	public function categoryAddSubFormSucceeded(ConferenceCategoryAddSubForm $form) {
		$formValues = $form->getValuesTransformed();

		$record = $this->conferenceCategoryModel->insert($formValues);

		if($record) {
			$this->template->subcategoryAdded = true;

			if ($this->presenter->isAjax()) {
				$this->redrawControl('categoryAddSubForm');
			} else $this->redirect('this');

			$this->onAddSub($record);
		}
	}

	public function handleDelete($id) {
		$record = $this->conferenceCategoryModel->find($id);
		if($record) {

			$record->toArray(); // load the object to be passed to the callback

			$this->conferenceCategoryModel->deleteCategoryTreeBranch($id);

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
		$conferenceCategory = $this->conferenceCategoryModel->find($id);

		$this["categoryEditForm"]->setDefaults($conferenceCategory); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('categoryEditForm');
		}
	}

	public function handleAddSub($id) {
		$conferenceCategory = $this->conferenceCategoryModel->find($id);

		$this["categoryAddSubForm"]["parent_id"]->setValue($id); // set up parent id

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('categoryAddSubForm');
		}
	}

	public function handleShowRelatedConferences($id){

		$result = array();
		$categoriesBranchIds = $this->conferenceCategoryModel->getCategoriesTreeIds($id);

		foreach ($categoriesBranchIds as $row) {
			$categories = $this->conferenceHasCategoryModel->findAllBy(array("conference_category_id" => $row['id']));
			array_push($result, array('categories' => $row, 'conference_has_category' => $categories));
		}

		$this->template->conferencesRelatedToCategory = $result;

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('conferencesRelatedToCategory');
		}
	}


}