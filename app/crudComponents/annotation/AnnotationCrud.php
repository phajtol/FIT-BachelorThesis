<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.5.2015
 * Time: 11:55
 */

namespace App\CrudComponents\Annotation;


class AnnotationCrud extends \App\CrudComponents\BaseCrudComponent {



	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var  \App\Model\Annotation */
	protected $annotationModel;

	protected $publicationId;

	public function __construct(
		$publicationId,
		\Nette\Security\User $loggedUser, \App\Model\Annotation $annotationModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL
	) {
		parent::__construct($parent, $name);

		$this->addDefaultTemplateVars(array(
			'entityAdded'   =>  false,
			'entityEdited'  =>  false,
			'entityDeleted' =>  false,
		));

		$this->publicationId = $publicationId;
		$this->annotationModel = $annotationModel;
		$this->loggedUser = $loggedUser;

		$this->onControlsCreate[] = function(\App\CrudComponents\BaseCrudControlsComponent &$controlsComponent) {
			//
		};
	}

	public function createComponentAddForm($name){
		$form = new AnnotationAddForm($this, $name);
		$form->onError[] = function(){
			$this->redrawControl('addForm');
		};
		$form->onSuccess[] = $this->addFormSucceeded;
	}

	public function createComponentEditForm($name){
		$form = new AnnotationEditForm($this, $name);
		$form->onError[] = function(){
			$this->redrawControl('editForm');
		};
		$form->onSuccess[] = $this->editFormSucceeded;
	}

	public function addFormSucceeded(AnnotationAddForm $form) {
		if(!$this->isActionAllowed('add')) return;

		$formValues = $form->getValuesTransformed();

		$formValues['submitter_id'] = $this->loggedUser->id;
		$formValues['publication_id'] = $this->publicationId;
		$formValues['date'] = new \Nette\Utils\DateTime();

		$record = $this->annotationModel->insert($formValues);

		if($record) {
			$this->template->entityAdded = true;

			if ($this->presenter->isAjax()) {
				$form->clearValues();
				$this->redrawControl('addForm');
			} else $this->redirect('this');

			$this->onAdd($record);
		}
	}

	public function editFormSucceeded(AnnotationEditForm $form) {
		if(!$this->isActionAllowed('edit')) return;

		$formValues = $form->getValuesTransformed();

		$formValues['submitter_id'] = $this->loggedUser->id;
		$formValues['date'] = new \Nette\Utils\DateTime();

		$this->annotationModel->update($formValues);
		$record = $this->annotationModel->find($formValues['id']);

		$this->template->entityEdited = true;

		if($this->presenter->isAjax()) {
			$this->redrawControl('editForm');
		} else $this->redirect('this');

		$this->onEdit($record);
	}


	public function handleDelete($id) {
		if(!$this->isActionAllowed('delete')) return;

		$record = $this->annotationModel->find($id);
		if($record) {

			$record->toArray(); // load the object to be passed to the callback

			$this->annotationModel->deleteAssociatedRecords($id);

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

		$annotation = $this->annotationModel->find($id);

		$this["editForm"]->setDefaults($annotation); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('editForm');
		}

	}

}