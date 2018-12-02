<?php

namespace App\CrudComponents\DocumentIndex;

use App\CrudComponents\BaseCrudControlsComponent;


class DocumentIndexCrud extends \App\CrudComponents\BaseCrudComponent {

	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var  \App\Model\DocumentIndex */
	protected $documentIndexModel;

	/** @var  \App\Model\ConferenceYearIsIndexed */
	protected $conferenceYearIsIndexedModel;


    /**
     * DocumentIndexCrud constructor.
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\DocumentIndex $documentIndexModel
     * @param \App\Model\ConferenceYearIsIndexed $conferenceYearIsIndexedModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
		\Nette\Security\User $loggedUser,
        \App\Model\DocumentIndex $documentIndexModel,
        \App\Model\ConferenceYearIsIndexed $conferenceYearIsIndexedModel,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->addDefaultTemplateVars([
			'entityAdded'   =>  false,
			'entityEdited'  =>  false,
			'entityDeleted' =>  false,
		]);

		$this->documentIndexModel = $documentIndexModel;
		$this->conferenceYearIsIndexedModel = $conferenceYearIsIndexedModel;
		$this->loggedUser = $loggedUser;

		$this->onControlsCreate[] = function (BaseCrudControlsComponent &$controlsComponent) {
			//
		};
	}

    /**
     * @param string $name
     * @return DocumentIndexAddForm
     */
	public function createComponentAddForm(string $name): DocumentIndexAddForm
    {
        $form = new DocumentIndexAddForm($this->documentIndexModel, $this, $name);

        $form->onError[] = function () {
            $this->redrawControl('addForm');
        };

        $form->onSuccess[] = function (DocumentIndexAddForm $form) {
            if (!$this->isActionAllowed('add')) {
                return;
            }

		    $formValues = $form->getValuesTransformed();
    		$record = $this->documentIndexModel->insert($formValues);

	    	if($record) {
		    	$this->template->entityAdded = true;

			    if ($this->presenter->isAjax()) {
				    $form->clearValues();
				    $this->redrawControl('addForm');
			    } else {
			        $this->redirect('this');
                }

			    $this->onAdd($record);
		    }
        };

        return $form;
	}

    /**
     * @param string $name
     * @return DocumentIndexEditForm
     */
	public function createComponentEditForm(string $name): DocumentIndexEditForm
    {
        $form = new DocumentIndexEditForm($this, $name);

        $form->onError[] = function () {
            $this->redrawControl('editForm');
        };

        $form->onSuccess[] = function (DocumentIndexEditForm $form) {
		    if (!$this->isActionAllowed('edit')) {
		        return;
            }

		    $formValues = $form->getValuesTransformed();
		    $this->documentIndexModel->update($formValues);
		    $record = $this->documentIndexModel->find($formValues['id']);
		    $this->template->entityEdited = true;

		    if ($this->presenter->isAjax()) {
			    $this->redrawControl('editForm');
		    } else {
		        $this->redirect('this');
            }

		    $this->onEdit($record);
        };

        return $form;
	}

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleDelete(int $id): void
    {
		if (!$this->isActionAllowed('delete')) {
		    return;
        }

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

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleEdit(int $id): void
    {
		if (!$this->isActionAllowed('edit')) {
		    return;
        }

		$documentIndex = $this->documentIndexModel->find($id);
		$this['editForm']->setDefaults($documentIndex); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('editForm');
		}
	}

}