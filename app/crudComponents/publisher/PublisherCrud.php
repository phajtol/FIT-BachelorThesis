<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 14.3.2015
 * Time: 23:02
 */

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

	public function __construct(\Nette\Security\User $loggedUser, \App\Model\Publisher $publisherModel,
								\App\Model\Publication $publicationModel, \App\Model\ConferenceYear $conferenceYearModel,
								\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

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

		$this->onControlsCreate[] = function(BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showPublisherRelated');
		};
	}

	public function createComponentPublisherAddForm(){
		$form = new PublisherAddForm($this->publisherModel, $this, 'publisherAddForm');
		$form->onSuccess[] = function(PublisherAddForm $form) {

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
            $form->onError[] = function($form) {
                $this->redrawControl('publisherAddForm');
            };
            return $form;
	}

	public function createComponentPublisherEditForm(){
            $form = new PublisherEditForm($this, 'publisherEditForm');
            $form->onSuccess[] = function(PublisherEditForm $form) {

		$formValues = $form->getValues();

		$formValues['submitter_id'] = $this->loggedUser->id;


		$this->publisherModel->update($formValues);

		$this->template->publisherEdited = true;

		$row = $this->publisherModel->findOneById($formValues['id']);

		$form->setValues(array());

		$this->onEdit($row);

		if(!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('publisherEditForm');
		}

            };
            $form->onError[] = function($form) {
                $this->redrawControl('publisherEditForm');
            };
            return $form;
        }

	public function handleDelete($publisherId) {

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

	public function handleEdit($publisherId) {
		$publisher = $this->publisherModel->find($publisherId);

		$this["publisherEditForm"]->setDefaults($publisher); // set up new values

		if ($this->presenter->isAjax()) {
			$this->redrawControl('publisherEditForm');
		}
	}



	public function handleShowPublisherRelated($publisherId) {
		$publication = $this->publicationModel->findAllBy(array("publisher_id" => $publisherId));
		$conferenceYear = $this->conferenceYearModel->findAllBy(array("publisher_id" => $publisherId));

		$this->template->publisherRelated_publication = $publication;
		$this->template->publisherRelated_conference_year = $conferenceYear;

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('publisherRelated');
		}
	}

}