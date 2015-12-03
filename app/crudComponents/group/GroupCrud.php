<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.5.2015
 * Time: 22:50
 */

namespace App\CrudComponents\Group;


class GroupCrud extends \App\CrudComponents\BaseCrudComponent {

	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var  \App\Model\Group */
	protected $groupModel;

	/** @var  \App\Model\GroupHasPublication */
	protected $groupHasPublicationModel;


	public function __construct(

		\Nette\Security\User $loggedUser, \App\Model\Group $groupModel, \App\Model\GroupHasPublication $groupHasPublicationModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL
	) {
		parent::__construct($parent, $name);

		$this->addDefaultTemplateVars(array(
			'entityAdded'   =>  false,
			'entityEdited'  =>  false,
			'entityDeleted' =>  false,
			'relatedPublications'   =>  array()
		));

		$this->groupModel = $groupModel;
		$this->groupHasPublicationModel = $groupHasPublicationModel;
		$this->loggedUser = $loggedUser;

		$this->onControlsCreate[] = function(\App\CrudComponents\BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedPublications');
		};
	}

	public function createComponentAddForm($name){
		$form = new GroupAddForm($this->groupModel, $this, $name);
		$form->onError[] = function(){
			$this->redrawControl('addForm');
		};
		$form->onSuccess[] = $this->addFormSucceeded;
	}

	public function createComponentEditForm($name){
		$form = new GroupEditForm($this, $name);
		$form->onError[] = function(){
			$this->redrawControl('editForm');
		};
		$form->onSuccess[] = $this->editFormSucceeded;
	}

	public function addFormSucceeded(GroupAddForm $form) {
		if(!$this->isActionAllowed('add')) return;

		$formValues = $form->getValuesTransformed();

		$record = $this->groupModel->insert($formValues);

		if($record) {
			$this->template->entityAdded = true;

			if ($this->presenter->isAjax()) {
				$form->clearValues();
				$this->redrawControl('addForm');
			} else $this->redirect('this');

			$this->onAdd($record);
		}
	}

	public function editFormSucceeded(GroupEditForm $form) {
		if(!$this->isActionAllowed('edit')) return;

		$formValues = $form->getValuesTransformed();

		$this->groupModel->update($formValues);
		$record = $this->groupModel->find($formValues['id']);

		$this->template->entityEdited = true;

		if($this->presenter->isAjax()) {
			$this->redrawControl('editForm');
		} else $this->redirect('this');

		$this->onEdit($record);
	}


	public function handleDelete($id) {
		if(!$this->isActionAllowed('delete')) return;

		$record = $this->groupModel->find($id);
		if($record) {

			$record->toArray(); // load the object to be passed to the callback

			$this->groupModel->deleteAssociatedRecords($id);

			$this->template->entityDeleted = true;

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deleteEntity');
			}

			$this->onDelete($record);
		}
	}

	public function handleEdit($id) {
		if(!$this->isActionAllowed('edit')) return;

		$group = $this->groupModel->find($id);

		$this["editForm"]->setDefaults($group); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('editForm');
		}

	}

	public function handleShowRelatedPublications($id) {
		$this->template->publicationsRelated =
			$this->groupHasPublicationModel->findAllBy(array("group_id" => $id));

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('relatedPublications');
		}
	}

}