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

	/** @var  \App\Model\JournalIsbn */
	protected $journalIsbnModel;


	public function __construct(

		\Nette\Security\User $loggedUser, \App\Model\Journal $journalModel,
		\App\Model\Publication $publicationModel,
		\App\Model\JournalIsbn $journalIsbn,
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
		$this->journalIsbnModel = $journalIsbn;

		$this->onControlsCreate[] = function(BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedPublications');
		};
	}

	public function createComponentJournalForm($name) {
            $form = new \App\CrudComponents\Journal\JournalForm($this->journalModel, $this, $name);
            $form->onError[] = function(){
                    $this->redrawControl('journalForm');
            };
            $form->onSuccess[] = function(JournalForm $form) {
		$formValues = $form->getValuesTransformed();

		$formValues['submitter_id'] = $this->loggedUser->id;

		unset($formValues['isbn']);
		unset($formValues['isbn_count']);

		if (empty($formValues['id'])) {
			$record = $this->journalModel->insert($formValues);
			$this->template->journalAdded = true;
			$this->onAdd($record);
		} else {
			$this->journalModel->update($formValues);
			$record = $this->journalModel->find($formValues['id']);
			$this->template->journalEdited = true;
			$this->onEdit($record);
		}

		$formValues = $form->getValuesTransformed();

		$this->journalIsbnModel->findAllBy(["journal_id" => $record->id])
											->delete();

		if (!empty($formValues['isbn'])) {
			foreach ($formValues['isbn'] as $isbn) {
				if (empty($isbn['isbn']) && empty($isbn['note']) ) {
					continue;
				}
				$this->journalIsbnModel->insert(["journal_id" => $record->id,
																	"isbn" => $isbn['isbn'],
																	"type" => $isbn['type'],
																	"note" => $isbn['note']]);
			}
		}




		if($record) {

			if ($this->presenter->isAjax()) {
				$form->clearValues();
				$this->redrawControl('journalForm');
			} else {
				$this->redirect('this');
			}

		}
    };
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

		$this["journalForm"]->setDefaults($journal); // set up new values

		$cont = $this['journalForm']['isbn'];
		$isbn = $this->journalIsbnModel->findAllBy(["journal_id" => $journal->id]);
		$i = 0;
		$count = count($isbn);
		$this['journalForm']->setIsbnCount($count);

		$this['journalForm']->addIsbn();

		foreach ($isbn as $row) {
			$cont[$i]['isbn']->setDefaultValue($row['isbn']);
			$cont[$i]['type']->setDefaultValue($row['type']);
			$cont[$i]['note']->setDefaultValue($row['note']);
			$i++;
		}

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('journalForm');
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

	public function handleAdd() {
		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('journalForm');
		}
	}

	public function handleAddIsbn($count) {
		$this['journalForm']['isbn_count']->setValue($count);
		$this['journalForm']->addIsbn();

		$this->redrawControl('isbn_count');

		$this->redrawControl("add_isbn");
		$this->redrawControl("last_isbn");

	}

	public function createComponentAddButton(){
		$sc = parent::createComponentAddButton();
		$sc->template->addLink =  $this->link('add!');
		return $sc;
	}

	public function render() {
		$this->template->journalForm = $this['journalForm'];
		parent::render();
	}
}
