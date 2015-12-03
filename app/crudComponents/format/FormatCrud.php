<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 15.4.2015
 * Time: 16:54
 */

namespace App\CrudComponents\Format;


use App\CrudComponents\BaseCrudControlsComponent;

class FormatCrud extends \App\CrudComponents\BaseCrudComponent {


	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var  \App\Model\Format */
	protected $formatModel;


	public function __construct(

		\Nette\Security\User $loggedUser, \App\Model\Format $formatModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL
	) {
		parent::__construct($parent, $name);

		$this->addDefaultTemplateVars(array(
			'entityAdded'   =>  false,
			'entityEdited'  =>  false,
			'entityDeleted' =>  false,
		));

		$this->formatModel = $formatModel;
		$this->loggedUser = $loggedUser;

		$this->onControlsCreate[] = function(BaseCrudControlsComponent &$controlsComponent) {
			//
		};
	}

	public function createComponentAddForm($name){
		$form = new FormatAddForm($this->formatModel, $this, $name);
		$form->onError[] = function(){
			$this->redrawControl('addForm');
		};
		$form->onSuccess[] = $this->addFormSucceeded;
	}

	public function createComponentEditForm($name){
		$form = new FormatEditForm($this, $name);
		$form->onError[] = function(){
			$this->redrawControl('editForm');
		};
		$form->onSuccess[] = $this->editFormSucceeded;
	}

	public function addFormSucceeded(FormatAddForm $form) {
		if(!$this->isActionAllowed('add')) return;

		$formValues = $form->getValuesTransformed();

		$formValues['timestamp'] = microtime(true);

		$record = $this->formatModel->insert($formValues);

		if($record) {
			$this->template->entityAdded = true;

			if ($this->presenter->isAjax()) {
				$form->clearValues();
				$this->redrawControl('addForm');
			} else $this->redirect('this');

			$this->onAdd($record);
		}
	}

	public function editFormSucceeded(FormatEditForm $form) {
		if(!$this->isActionAllowed('edit')) return;

		$formValues = $form->getValuesTransformed();

		$formValues['timestamp'] = microtime(true);

		$this->formatModel->update($formValues);
		$record = $this->formatModel->find($formValues['id']);

		$this->template->entityEdited = true;

		if($this->presenter->isAjax()) {
			$this->redrawControl('editForm');
		} else $this->redirect('this');

		$this->onEdit($record);
	}


	public function handleDelete($id) {
		if(!$this->isActionAllowed('delete')) return;

		$record = $this->formatModel->find($id);
		if($record) {

			$record->toArray(); // load the object to be passed to the callback

			$this->formatModel->deleteAssociatedRecords($id);

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

		$format = $this->formatModel->find($id);

		$this["editForm"]->setDefaults($format); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('editForm');
		}

	}


}