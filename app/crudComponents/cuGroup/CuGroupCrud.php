<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.4.2015
 * Time: 2:51
 */

namespace App\CrudComponents\CuGroup;


use App\CrudComponents\BaseCrudComponent;
use App\CrudComponents\BaseCrudControlsComponent;

class CuGroupCrud extends BaseCrudComponent {

	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var \App\Model\CuGroup */
	protected $cuGroupModel;

	/** @var \App\Model\SubmitterHasCuGroup */
	protected $submitterHasCuGroupModel;

	/** @var \App\Factories\IConferenceCategoryListFactory */
	protected $conferenceCategoryListFactory;

	/**
	 * @var \App\Model\CuGroupHasConferenceCategory
	 */
	protected $cuGroupHasConferenceCategoryModel;


	public function __construct(

		\Nette\Security\User $loggedUser, \App\Model\CuGroup $cuGroupModel, \App\Model\SubmitterHasCuGroup $submitterHasCuGroupModel, \App\Model\CuGroupHasConferenceCategory $cuGroupHasConferenceCategoryModel,
		\App\Factories\IConferenceCategoryListFactory $conferenceCategoryListFactory,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL
	) {
		parent::__construct($parent, $name);

		$this->cuGroupModel = $cuGroupModel;
		$this->submitterHasCuGroupModel = $submitterHasCuGroupModel;
		$this->cuGroupHasConferenceCategoryModel = $cuGroupHasConferenceCategoryModel;
		$this->loggedUser = $loggedUser;

		$this->conferenceCategoryListFactory = $conferenceCategoryListFactory;

		$this->onControlsCreate[] = function(BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedUsers');
		};
	}

	public function createComponentCuGroupAddForm($name){
            $form = new CuGroupAddForm($this->cuGroupModel, $this, $name);
            $form->onError[] = function(){
                    $this->redrawControl('cuGroupAddForm');
            };
            $form->onSuccess[] = function(CuGroupAddForm $form) {
                $formValues = $form->getValuesTransformed();

		$conferenceCategories = $formValues['conference_categories'] ? explode(" ", $formValues['conference_categories']) : array();
		unset($formValues['conference_categories']);

		$record = $this->cuGroupModel->insert($formValues);

		if($record) {
			$this->template->cuGroupAdded = true;

			$this->cuGroupHasConferenceCategoryModel->setAssociatedConferenceCategories($record->id, $conferenceCategories);

			if ($this->presenter->isAjax()) {
				$form->clearValues();
				$this->redrawControl('cuGroupAddForm');
			} else $this->redirect('this');

			$this->onAdd($record);
		}
            };
	}

	public function createComponentCuGroupEditForm($name){
            $form = new CuGroupEditForm($this, $name);
            $form->onError[] = function(){
                    $this->redrawControl('cuGroupEditForm');
            };
            $form->onSuccess[] = function(CuGroupEditForm $form) {
		$formValues = $form->getValuesTransformed();

		$conferenceCategories = $formValues['conference_categories'] ? explode(" ", $formValues['conference_categories']) : array();
		unset($formValues['conference_categories']);

		$this->cuGroupModel->update($formValues);
		$record = $this->cuGroupModel->find($formValues['id']);

		$this->cuGroupHasConferenceCategoryModel->setAssociatedConferenceCategories($record->id, $conferenceCategories);

		$this->template->cuGroupEdited = true;

		if($this->presenter->isAjax()) {
			$this->redrawControl('cuGroupEditForm');
		} else $this->redirect('this');

		$this->onEdit($record);
            };
	}

	public function handleDelete($id) {
		$record = $this->cuGroupModel->find($id);
		if($record) {

			$record->toArray(); // load the object to be passed to the callback

			$this->cuGroupModel->deleteAssociatedRecords($id);

			$this->template->cuGroupDeleted = true;

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deleteCuGroup');
			}

			$this->onDelete($record);
		}
	}

	public function handleEdit($id) {
		$cuGroup = $this->cuGroupModel->find($id);

		$conference_categories_res = $this->cuGroupHasConferenceCategoryModel->findAllBy(array('cu_group_id' => $id));
		$conference_category_ids = [];
		foreach($conference_categories_res as $conference_category) $conference_category_ids[] = $conference_category->conference_category_id;

		$this['cuGroupEditForm']->setDefaults($cuGroup);
		$this['cuGroupEditForm']['conference_categories']->setValue(implode(' ', $conference_category_ids));

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('cuGroupEditForm');
		}

	}

	public function handleShowRelatedUsers($id) {

		$users = [];
		$rels = $this->submitterHasCuGroupModel->getAllByCuGroupId($id);

		foreach($rels as $rel) {
			foreach($rel->related('submitter') as $submitter) $users[] = $submitter;
		}

		$this->template->usersRelatedToCuGroup = $users;

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('usersRelatedToCuGroup');
		}
	}

	public function render() {

		$this->template->addFormConferenceCategoriesElementId = $this['cuGroupAddForm']['conference_categories']->getHtmlId();
		$this->template->editFormConferenceCategoriesElementId = $this['cuGroupEditForm']['conference_categories']->getHtmlId();

		$this->addDefaultTemplateVars(array(
			"cuGroupAdded" => false,
			"cuGroupEdited" => false,
			"cuGroupDeleted" => false,
			"usersRelatedToCuGroup" => array()
		));

		parent::render();
	}


	public function createComponentConferenceCategoryListA(){ return $this->createComponentConferenceCategoryList(); }
	public function createComponentConferenceCategoryListE(){ return $this->createComponentConferenceCategoryList(); }

	protected function createComponentConferenceCategoryList(){
		$c = $this->conferenceCategoryListFactory->create();

		$c->setHasControls(true);
		$c->setIsSelectable(true);
		$c->setHasThreeStates(true);

		$c->setHeight('250px');

		return $c;
	}

}