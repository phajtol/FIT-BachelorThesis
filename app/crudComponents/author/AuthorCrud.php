<?php

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


    /**
     * AuthorCrud constructor.
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\Submitter $submitterModel
     * @param \App\Model\Author $authorModel
     * @param \App\Model\AuthorHasPublication $authorHasPublicationModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
		\Nette\Security\User $loggedUser,
        \App\Model\Submitter $submitterModel,
        \App\Model\Author $authorModel,
        \App\Model\AuthorHasPublication $authorHasPublicationModel,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
        parent::__construct();
        if ($parent) {
            $parent->addComponent($this, $name);
        }

		$this->addDefaultTemplateVars([
			'authorAdded' => false,
			'authorEdited' => false,
			'authorDeleted' => false,
			'publicationsRelatedToAuthor' => []
		]);

		$this->submitterModel = $submitterModel;
		$this->authorModel = $authorModel;
		$this->loggedUser = $loggedUser;
		$this->authorHasPublicationModel = $authorHasPublicationModel;

		$this->onControlsCreate[] = function (BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedPublications');
		};
	}

    /**
     * @param string $name
     * @return AuthorAddForm
     */
	public function createComponentAuthorAddForm(string $name): AuthorAddForm
    {
        $form = new AuthorAddForm($this->submitterModel,$this->authorModel, $this, $name);

        $form->onError[] = function () {
            $this->redrawControl('authorAddForm');
        };

        $form->onSuccess[] = function (AuthorAddForm $form) {
            $formValues = $form->getValuesTransformed();

		    $formValues['submitter_id'] = $this->loggedUser->id;

		    $record = $this->authorModel->insert($formValues);

		    if ($record) {
			    $this->template->authorAdded = true;

			    if ($this->presenter->isAjax()) {
				    $form->clearValues();
				    $this->redrawControl('authorAddForm');
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
     * @return AuthorEditForm
     */
	public function createComponentAuthorEditForm(string $name): AuthorEditForm
    {
        $form = new AuthorEditForm($this->submitterModel,$this, $name);

        $form->onError[] = function () {
            $this->redrawControl('authorEditForm');
        };

        $form->onSuccess[] = function (AuthorEditForm $form) {
            $formValues = $form->getValuesTransformed();

		    $formValues['submitter_id'] = $this->loggedUser->id;

		    $this->authorModel->update($formValues);
		    $record = $this->authorModel->find($formValues['id']);

		    $this->template->authorEdited = true;

		    if($this->presenter->isAjax()) {
			    $this->redrawControl('authorEditForm');
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
		$record = $this->authorModel->find($id);

		if ($record) {
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

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleEdit(int $id): void
    {
		$author = $this->authorModel->find($id);

		$this['authorEditForm']->setDefaults($author); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('authorEditForm');
		}
	}

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleShowRelatedPublications(int $id): void {
		$this->template->publicationsRelatedToAuthor = $this->authorHasPublicationModel->findAllBy(["author_id" => $id]);

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('publicationsRelatedToAuthor');
		}
	}

}
