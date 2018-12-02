<?php

namespace App\CrudComponents\Journal;

use App\Components\StaticContentComponent;
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


    /**
     * JournalCrud constructor.
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\Journal $journalModel
     * @param \App\Model\Publication $publicationModel
     * @param \App\Model\JournalIsbn $journalIsbn
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
		\Nette\Security\User $loggedUser,
        \App\Model\Journal $journalModel,
		\App\Model\Publication $publicationModel,
		\App\Model\JournalIsbn $journalIsbn,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
        parent::__construct();
        if ($parent) {
            $parent->addComponent($this, $name);
        }

		$this->addDefaultTemplateVars([
			'journalAdded'      =>  false,
			'journalEdited'     =>  false,
			'journalDeleted'    =>  false,
			'publicationsRelatedToJournal'    =>  [],
		]);

		$this->journalModel = $journalModel;
		$this->loggedUser = $loggedUser;
		$this->publicationModel = $publicationModel;
		$this->journalIsbnModel = $journalIsbn;

		$this->onControlsCreate[] = function (BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedPublications');
		};
	}

    /**
     * @param string $name
     * @return JournalForm
     */
	public function createComponentJournalForm(string $name): JournalForm
    {
        $form = new JournalForm($this->journalModel, $this, $name);

        $form->onError[] = function(){
            $this->redrawControl('journalForm');
        };

        $form->onSuccess[] = function (JournalForm $form) {
		    $formValues = $form->getValuesTransformed();
		    $formValues['submitter_id'] = $this->loggedUser->id;

		    unset($formValues['isbn']);
		    unset($formValues['isbn_count']);

		    if (empty($formValues['id'])) {
		        unset($formValues['id']);
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
		    $this->journalIsbnModel->findAllBy(["journal_id" => $record->id])->delete();

		    if (!empty($formValues['isbn'])) {
			    foreach ($formValues['isbn'] as $isbn) {
				    if (empty($isbn['isbn']) && empty($isbn['note'])) {
					    continue;
				    }

				    $this->journalIsbnModel->insert([
				        "journal_id" => $record->id,
                        "isbn" => $isbn['isbn'],
                        "type" => $isbn['type'],
                        "note" => $isbn['note']
                    ]);
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

        return $form;
	}

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleDelete(int $id): void
    {
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

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleEdit(int $id): void
    {
		$journal = $this->journalModel->find($id);
		$this['journalForm']->setDefaults($journal); // set up new values
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

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleShowRelatedPublications(int $id): void
    {
		$this->template->publicationsRelatedToJournal = $this->publicationModel->findAllBy(["journal_id" => $id]);

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('publicationsRelatedToJournal');
		}
	}

    /**
     * @throws \Nette\Application\AbortException
     */
	public function handleAdd(): void
    {
		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('journalForm');
		}
	}

    /**
     * @param int $count
     */
	public function handleAddIsbn(int $count): void
    {
		$this['journalForm']['isbn_count']->setValue($count);
		$this['journalForm']->addIsbn();

		$this->redrawControl('isbn_count');
		$this->redrawControl("add_isbn");
		$this->redrawControl("last_isbn");
	}

    /**
     * @return StaticContentComponent
     * @throws \Nette\Application\UI\InvalidLinkException
     */
	public function createComponentAddButton(): StaticContentComponent
    {
		$sc = parent::createComponentAddButton();
		$sc->template->addLink =  $this->link('add!');
		return $sc;
	}

    /**
     *
     */
	public function render(): void
    {
		$this->template->journalForm = $this['journalForm'];
		parent::render();
	}
}
