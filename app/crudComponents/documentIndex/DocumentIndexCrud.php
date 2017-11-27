<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 15.4.2015
 * Time: 16:54
 */

namespace App\CrudComponents\DocumentIndex;


use App\CrudComponents\BaseCrudControlsComponent;

class DocumentIndexCrud extends \App\CrudComponents\BaseCrudComponent {


	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var  \App\Model\DocumentIndex */
	protected $documentIndexModel;

	/** @var  \App\Model\ConferenceYearIsIndexed */
	protected $conferenceYearIsIndexedModel;


	public function __construct(

		\Nette\Security\User $loggedUser, \App\Model\DocumentIndex $documentIndexModel, \App\Model\ConferenceYearIsIndexed $conferenceYearIsIndexedModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL
	) {
		parent::__construct($parent, $name);

		$this->addDefaultTemplateVars(array(
			'entityAdded'   =>  false,
			'entityEdited'  =>  false,
			'entityDeleted' =>  false,
		));

		$this->documentIndexModel = $documentIndexModel;
		$this->conferenceYearIsIndexedModel = $conferenceYearIsIndexedModel;
		$this->loggedUser = $loggedUser;

		$this->onControlsCreate[] = function(BaseCrudControlsComponent &$controlsComponent) {
			//
		};
	}

	public function createComponentAddForm($name){
            $form = new DocumentIndexAddForm($this->documentIndexModel, $this, $name);
            $form->onError[] = function(){
                    $this->redrawControl('addForm');
            };
            $form->onSuccess[] = function(DocumentIndexAddForm $form) {
                if(!$this->isActionAllowed('add')) return;

		$formValues = $form->getValuesTransformed();

		$record = $this->documentIndexModel->insert($formValues);

		if($record) {
			$this->template->entityAdded = true;

			if ($this->presenter->isAjax()) {
				$form->clearValues();
				$this->redrawControl('addForm');
			} else $this->redirect('this');

			$this->onAdd($record);
		}
            };
	}

	public function createComponentEditForm($name){
            $form = new DocumentIndexEditForm($this, $name);
            $form->onError[] = function(){
                    $this->redrawControl('editForm');
            };
            $form->onSuccess[] = function(DocumentIndexEditForm $form) {
		if(!$this->isActionAllowed('edit')) return;

		$formValues = $form->getValuesTransformed();

		$this->documentIndexModel->update($formValues);
		$record = $this->documentIndexModel->find($formValues['id']);

		$this->template->entityEdited = true;

		if($this->presenter->isAjax()) {
			$this->redrawControl('editForm');
		} else $this->redirect('this');

		$this->onEdit($record);
            };
	}

	public function handleDelete($id) {
		if(!$this->isActionAllowed('delete')) return;

		$record = $this->documentIndexModel->find($id);
		if($record) {

			$record->toArray(); // load the object to be passed to the callback

			$this->documentIndexModel->deleteWithAssociatedRecords($id);

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

		$documentIndex = $this->documentIndexModel->find($id);

		$this["editForm"]->setDefaults($documentIndex); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('editForm');
		}

	}


}