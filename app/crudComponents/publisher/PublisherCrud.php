<?php

namespace App\CrudComponents\Publisher;

use App\CrudComponents\BaseCrudComponent;
use App\CrudComponents\BaseCrudControlsComponent;


class PublisherCrud extends BaseCrudComponent {

	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var \App\Model\Publisher */
	protected $publisherModel;

	/** @var  \App\Model\ConferenceYear */
	protected $conferenceYearModel;

	/** @var  \App\Model\Publication */
	protected $publicationModel;


    /**
     * PublisherCrud constructor.
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\Publisher $publisherModel
     * @param \App\Model\Publication $publicationModel
     * @param \App\Model\ConferenceYear $conferenceYearModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\Nette\Security\User $loggedUser,
                                \App\Model\Publisher $publisherModel,
								\App\Model\Publication $publicationModel,
                                \App\Model\ConferenceYear $conferenceYearModel,
								\Nette\ComponentModel\IContainer $parent = NULL,
                                string $name = NULL)
	{
        parent::__construct();
        if ($parent) {
            $parent->addComponent($this, $name);
        }

		$this->addDefaultTemplateVars(array(
			"publisherAdded"        =>  false,
			"publisherEdited"       =>  false,
			"publisherDeleted"      =>  false,

			"publisherRelated_publication"     =>   null,
			"publisherRelated_conference_year" =>   null
		));

		$this->loggedUser = $loggedUser;
		$this->publisherModel = $publisherModel;
		$this->publicationModel = $publicationModel;
		$this->conferenceYearModel = $conferenceYearModel;

		$this->onControlsCreate[] = function (BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showPublisherRelated');
		};
	}

    /**
     * @return PublisherAddForm
     */
	public function createComponentPublisherAddForm(): PublisherAddForm
    {
		$form = new PublisherAddForm($this->publisherModel, $this, 'publisherAddForm');

        $form->onError[] = function (PublisherAddForm $form) {
            $this->redrawControl('publisherAddForm');
        };

        $form->onSuccess[] = function (PublisherAddForm $form) {
		    $formValues = $form->getValues();
		    unset($formValues['id']);
		    $this->template->publisherAdded = true;

		    $formValues['submitter_id'] = $this->loggedUser->id;

		    $record = $this->publisherModel->insert($formValues);

		    if (!$this->presenter->isAjax()) {
			    $this->presenter->redirect('this');
		    } else {
			    $form->clearValues();
			    $this->redrawControl('publisherAddForm');
		    }

		    $this->onAdd($record);
		};

        return $form;
	}

    /**
     * @return PublisherEditForm
     */
	public function createComponentPublisherEditForm(): PublisherEditForm
    {
        $form = new PublisherEditForm($this, 'publisherEditForm');

        $form->onError[] = function (PublisherEditForm $form) {
            $this->redrawControl('publisherEditForm');
        };

        $form->onSuccess[] = function (PublisherEditForm $form) {
            $formValues = $form->getValues();
            $formValues['submitter_id'] = $this->loggedUser->id;
            $this->publisherModel->update($formValues);
            $this->template->publisherEdited = true;

		    $row = $this->publisherModel->findOneById($formValues['id']);

		    $form->setValues([]);
		    $this->onEdit($row);

		    if(!$this->presenter->isAjax()) {
			    $this->presenter->redirect('this');
		    } else {
			    $this->redrawControl('publisherEditForm');
		    }

        };

        return $form;
    }

    /**
     * @param int $publisherId
     * @throws \Nette\Application\AbortException
     */
	public function handleDelete(int $publisherId): void
    {
		$record = $this->publisherModel->find($publisherId);

		if($record) {
			$record->toArray();
			$this->publisherModel->deleteWithAssociatedRecords($publisherId);
			$this->template->publisherDeleted = true;

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deletePublisher');
			}

			$this->onDelete($record);
		}
	}

    /**
     * @param int $publisherId
     */
	public function handleEdit(int $publisherId): void
    {
		$publisher = $this->publisherModel->find($publisherId);

		$this["publisherEditForm"]->setDefaults($publisher); // set up new values

		if ($this->presenter->isAjax()) {
			$this->redrawControl('publisherEditForm');
		}
	}

    /**
     * @param int $publisherId
     * @throws \Nette\Application\AbortException
     */
	public function handleShowPublisherRelated(int $publisherId): void
    {
		$publication = $this->publicationModel->findAllBy(['publisher_id' => $publisherId]);
		$conferenceYear = $this->conferenceYearModel->findAllBy(['publisher_id' => $publisherId]);
		$this->template->publisherRelated_publication = $publication;
		$this->template->publisherRelated_conference_year = $conferenceYear;

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('publisherRelated');
		}
	}

}