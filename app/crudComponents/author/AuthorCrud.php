<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 18.3.2015
 * Time: 17:27
 */

namespace App\CrudComponents\Author;


use App\CrudComponents\BaseCrudComponent;
use App\CrudComponents\BaseCrudControlsComponent;

class AuthorCrud extends BaseCrudComponent {

	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var \App\Model\Author */
	protected $authorModel;

	/** @var \App\Model\AuthorHasPublication */
	protected $authorHasPublicationModel;

	/** @var \App\Model\Submitter */
	protected $submitterModel;

	public function __construct(

		\Nette\Security\User $loggedUser, \App\Model\Submitter $submitterModel, \App\Model\Author $authorModel, \App\Model\AuthorHasPublication $authorHasPublicationModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL
	) {
		parent::__construct($parent, $name);

		$this->addDefaultTemplateVars(array(
			'authorAdded' => false,
			'authorEdited' => false,
			'authorDeleted' => false,
			'publicationsRelatedToAuthor' => array()
		));

		$this->submitterModel = $submitterModel;
		$this->authorModel = $authorModel;
		$this->loggedUser = $loggedUser;
		$this->authorHasPublicationModel = $authorHasPublicationModel;

		$this->onControlsCreate[] = function(BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedPublications');
		};
	}

	public function createComponentAuthorAddForm($name){
		$form = new AuthorAddForm($this->submitterModel,$this->authorModel, $this, $name);
		$form->onError[] = function(){
			$this->redrawControl('authorAddForm');
		};
		$form->onSuccess[] = $this->authorAddFormSucceeded;
	}

	public function createComponentAuthorEditForm($name){
		$form = new AuthorEditForm($this->submitterModel,$this, $name);
		$form->onError[] = function(){
			$this->redrawControl('authorEditForm');
		};
		$form->onSuccess[] = $this->authorEditFormSucceeded;
	}

	public function authorAddFormSucceeded(AuthorAddForm $form) {
		$formValues = $form->getValuesTransformed();

		$formValues['submitter_id'] = $this->loggedUser->id;

		$record = $this->authorModel->insert($formValues);

		if($record) {
			$this->template->authorAdded = true;

			if ($this->presenter->isAjax()) {
				$form->clearValues();
				$this->redrawControl('authorAddForm');
			} else $this->redirect('this');

			$this->onAdd($record);
		}
	}

	public function authorEditFormSucceeded(AuthorEditForm $form) {
		$formValues = $form->getValuesTransformed();

		$formValues['submitter_id'] = $this->loggedUser->id;

		$this->authorModel->update($formValues);
		$record = $this->authorModel->find($formValues['id']);

		$this->template->authorEdited = true;

		if($this->presenter->isAjax()) {
			$this->redrawControl('authorEditForm');
		} else $this->redirect('this');

		$this->onEdit($record);
	}


	public function handleDelete($id) {
		$record = $this->authorModel->find($id);
		if($record) {

			$record->toArray(); // load the object to be passed to the callback

			$this->authorModel->deleteAssociatedRecords($id);

			$this->template->authorDeleted = true;

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deleteAuthor');
			}

			$this->onDelete($record);
		}
	}

	public function handleEdit($id) {
		$author = $this->authorModel->find($id);

		$this["authorEditForm"]->setDefaults($author); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('authorEditForm');
		}

	}

	public function handleShowRelatedPublications($id) {
		$this->template->publicationsRelatedToAuthor =
			$this->authorHasPublicationModel->findAllBy(array("author_id" => $id));

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('publicationsRelatedToAuthor');
		}
	}


}
