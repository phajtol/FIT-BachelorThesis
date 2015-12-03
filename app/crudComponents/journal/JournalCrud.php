<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 26.3.2015
 * Time: 12:58
 */

namespace App\CrudComponents\Journal;


use App\CrudComponents\BaseCrudComponent;
use App\CrudComponents\BaseCrudControlsComponent;

class JournalCrud extends BaseCrudComponent {

	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var \App\Model\Journal */
	protected $journalModel;

	/** @var \App\Model\Publication */
	protected $publicationModel;


	public function __construct(

		\Nette\Security\User $loggedUser, \App\Model\Journal $journalModel, \App\Model\Publication $publicationModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL
	) {
		parent::__construct($parent, $name);

		$this->addDefaultTemplateVars(array(
			'journalAdded'      =>  false,
			'journalEdited'     =>  false,
			'journalDeleted'    =>  false,
			'publicationsRelatedToJournal'    =>  array(),
		));

		$this->journalModel = $journalModel;
		$this->loggedUser = $loggedUser;
		$this->publicationModel = $publicationModel;

		$this->onControlsCreate[] = function(BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedPublications');
		};
	}

	public function createComponentJournalAddForm($name){
		$form = new JournalAddForm($this->journalModel, $this, $name);
		$form->onError[] = function(){
			$this->redrawControl('journalAddForm');
		};
		$form->onSuccess[] = $this->journalAddFormSucceeded;
	}

	public function createComponentJournalEditForm($name){
		$form = new JournalEditForm($this, $name);
		$form->onError[] = function(){
			$this->redrawControl('journalEditForm');
		};
		$form->onSuccess[] = $this->journalEditFormSucceeded;
	}

	public function journalAddFormSucceeded(JournalAddForm $form) {
		$formValues = $form->getValuesTransformed();

		$formValues['submitter_id'] = $this->loggedUser->id;

		$record = $this->journalModel->insert($formValues);

		if($record) {
			$this->template->journalAdded = true;

			if ($this->presenter->isAjax()) {
				$form->clearValues();
				$this->redrawControl('journalAddForm');
			} else $this->redirect('this');

			$this->onAdd($record);
		}
	}

	public function journalEditFormSucceeded(JournalEditForm $form) {
		$formValues = $form->getValuesTransformed();

		$formValues['submitter_id'] = $this->loggedUser->id;

		$this->journalModel->update($formValues);
		$record = $this->journalModel->find($formValues['id']);

		$this->template->journalEdited = true;

		if($this->presenter->isAjax()) {
			$this->redrawControl('journalEditForm');
		} else $this->redirect('this');

		$this->onEdit($record);
	}


	public function handleDelete($id) {
		$record = $this->journalModel->find($id);
		if($record) {

			$record->toArray(); // load the object to be passed to the callback

			$this->journalModel->deleteAssociatedRecords($id);

			$this->template->journalDeleted = true;

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deleteJournal');
			}

			$this->onDelete($record);
		}
	}

	public function handleEdit($id) {
		$journal = $this->journalModel->find($id);

		$this["journalEditForm"]->setDefaults($journal); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('journalEditForm');
		}

	}

	public function handleShowRelatedPublications($id) {
		$this->template->publicationsRelatedToJournal =
			$this->publicationModel->findAllBy(array("journal_id" => $id));

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('publicationsRelatedToJournal');
		}
	}

}
?>