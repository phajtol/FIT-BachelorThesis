<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 18.3.2015
 * Time: 1:42
 */

namespace App\CrudComponents\ConferenceYear;


use App\CrudComponents\BaseCrudComponent;
use App\CrudComponents\BaseCrudControlsComponent;
use App\Helpers\Func;

class ConferenceYearCrud extends BaseCrudComponent {

	/** @var \App\Model\ConferenceYear */
	protected $conferenceYearModel;

	/** @var \App\Model\Conference */
	protected $conferenceModel;

	/** @var  \App\Model\Publisher */
	protected $publisherModel;

	/** @var  \App\Model\Publication */
	protected $publicationModel;

	/** @var  \App\Model\DocumentIndex */
	protected $documentIndexModel;

	/** @var  \App\Model\ConferenceYearIsIndexed */
	protected $conferenceYearIsIndexedModel;

	/** @var  \Nette\Security\User */
	protected $loggedUser;

	protected $conferenceId;

	public $onArchivedStateChanged = [];

	public $onWorkshopsUpdate = [];

	public function __construct($conferenceId, \Nette\Security\User $loggedUser, \App\Model\Publisher $publisherModel,
								\App\Model\Publication $publicationModel, \App\Model\ConferenceYear $conferenceYearModel, \App\Model\Conference $conferenceModel,
								\App\Model\DocumentIndex $documentIndexModel, \App\Model\ConferenceYearIsIndexed $conferenceYearIsIndexedModel,
								\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		$this->conferenceYearModel = $conferenceYearModel;
		$this->conferenceModel = $conferenceModel;
		$this->publicationModel = $publicationModel;
		$this->publisherModel = $publisherModel;
		$this->documentIndexModel = $documentIndexModel;
		$this->conferenceYearIsIndexedModel = $conferenceYearIsIndexedModel;

		$this->loggedUser = $loggedUser;
		$this->conferenceId = $conferenceId;

		parent::__construct($parent, $name);

		$this->writeActions[] = 'archivedStateChange';
		$this->writeActions[] = 'manageWorkshops';
		$this->allowAction('archivedStateChange');
		$this->allowAction('showRelatedPublications');
		$this->allowAction('showWorkshops');
		$this->allowAction('manageWorkshops');

		$this->onControlsCreate[] = function(BaseCrudControlsComponent &$controlsComponent) use ($conferenceYearModel) {
			$controlsComponent->addActionAvailable('showRelatedPublications');
			$controlsComponent->addActionAvailable('showWorkshops');

			$conferenceYear = $conferenceYearModel->find($controlsComponent->getRecordId());

			if($conferenceYear) {
				$conferenceYearState = $conferenceYear->state;

				$conferenceYearSTM = array(
					'alive'     =>  'archived',
					'archived'  =>  'alive'
				);

				$controlsComponent->addTemplateVars(array(
					'conferenceYearState' => $conferenceYearState,
					'nextConferenceYearState' => $conferenceYearSTM[$conferenceYearState],
					'setConferenceYearStateLink' => $this->link('setConferenceYearState!', array($controlsComponent->getRecordId(), $conferenceYearSTM[$conferenceYearState]))
				));
			}
		};
	}

	/**
	 * @return \Nette\ComponentModel\IContainer
	 */
	public function getConferenceId() {
		return $this->conferenceId;
	}


	// post-construct
	protected function attached($presenter) {
		parent::attached($presenter);

		// load forms
		if($this->isActionAllowed('add'))
			$this->template->conferenceYearAddForm = $this['conferenceYearAddForm'];

		if($this->isActionAllowed('edit'))
			$this->template->conferenceYearEditForm = $this['conferenceYearEditForm'];

		// templates
		$this->template->conferenceYearAdded = false;
		$this->template->conferenceYearDeleted = false;
		$this->template->conferenceYearEdited = false;
		$this->template->relatedPublications = array();
		$this->template->relatedWorkshops = array();
	}


	public function createComponentPublisherA(){
		$publisherCrud = new \App\CrudComponents\Publisher\PublisherCrud(
			$this->loggedUser, $this->publisherModel, $this->publicationModel, $this->conferenceYearModel,
				$this, 'publisherA'
		);

		$p = $this;
		$publisherCrud->onAdd[] = function($record) use ($p) {
			$p->handleShowPublisherInfoA($record->id);
			$this->updatePublishers();
			$p["conferenceYearAddForm"]["publisher_id"]->setValue($record->id);
			$p->redrawControl('conferenceYearAddForm-publisher_id');
		};

		$publisherCrud->onDelete[] = function($record) use ($p) {
			$p->handleShowPublisherInfoA($record->id);
			$this->updatePublishers();
			$p["conferenceYearAddForm"]["publisher_id"]->setValue(null);
			$p->redrawControl('conferenceYearAddForm-publisher_id');
		};

		$publisherCrud->onEdit[] = function($record) use ($p) {
			$p->handleShowPublisherInfoA($record->id);
			$this->updatePublishers();
			$p["conferenceYearAddForm"]["publisher_id"]->setValue($record->id);
			//$p["conferenceYearEditForm"]->setValues(array('publisher_id', $record->id));
			$p->redrawControl('conferenceYearAddForm-publisher_id');
			//$p->redrawControl('conferenceYearEditForm-publisher_id');
		};

		return $publisherCrud;
	}

	public function createComponentPublisherE(){
		$publisherCrud = new \App\CrudComponents\Publisher\PublisherCrud(
			$this->loggedUser, $this->publisherModel, $this->publicationModel, $this->conferenceYearModel,
			$this, 'publisherE'
		);

		$p = $this;
		$publisherCrud->onAdd[] = function($record) use ($p) {
			$p->handleShowPublisherInfoE($record->id);
			$this->updatePublishers();
			$p["conferenceYearEditForm"]["publisher_id"]->setValue($record->id);
			$p->redrawControl('conferenceYearEditForm-publisher_id');
		};

		$publisherCrud->onDelete[] = function($record) use ($p) {
			$p->handleShowPublisherInfoE($record->id);
			$this->updatePublishers();
			$p["conferenceYearEditForm"]["publisher_id"]->setValue(null);
			$p->redrawControl('conferenceYearEditForm-publisher_id');
		};

		$publisherCrud->onEdit[] = function($record) use ($p) {
			$p->handleShowPublisherInfoE($record->id);
			$this->updatePublishers();
			$p["conferenceYearEditForm"]->setValues(array('publisher_id', $record->id));
			$p->redrawControl('conferenceYearEditForm-publisher_id');
		};

		return $publisherCrud;
	}

	protected function loadPublishers(){
		$publisherSet = $this->publisherModel->findAll()->order('name ASC');
		$publishers = array();
		foreach($publisherSet as $publisherRec) {
			$publishers[$publisherRec->id] = $publisherRec->name;
		}
		return $publishers;
	}

	protected function loadDocumentIndexes(){
		$documentIndexSet = $this->documentIndexModel->findAll()->order('name ASC');
		$documentIndexes = array();
		foreach($documentIndexSet as $documentIndex){
			$documentIndexes[$documentIndex->id] = $documentIndex->name;
		}
		return $documentIndexes;
	}

	protected function updatePublishers(){
		$this['conferenceYearAddForm']->setPublishers($this->loadPublishers());
		$this['conferenceYearEditForm']->setPublishers($this->loadPublishers());
		$this->redrawControl('conferenceYearEditForm-publisher_id');
		$this->redrawControl('conferenceYearAddForm-publisher_id');
	}

	public function createComponentConferenceYearAddForm(){
            if(!$this->isActionAllowed('add')) return null;
            $form = new ConferenceYearForm($this->conferenceId, $this->loadPublishers(), $this->loadDocumentIndexes(), $this->conferenceModel, $this->conferenceYearModel, $this->conferenceYearIsIndexedModel, $this, 'conferenceYearAddForm');
            $this->reduceForm($form);
            $form->onSuccess[] = function(ConferenceYearForm $form) {

		$formValues = $form->getValuesTransformed();

		$this->template->conferenceYearAdded = true;

		$formValues['submitter_id'] = intval($this->loggedUser->id);

		$documentIndexes = Func::getAndUnset($formValues, 'document_indexes');
		$this->sanitizeEntityData($formValues);

		$record = $this->conferenceYearModel->insert($formValues);

		$this->conferenceYearIsIndexedModel->setAssociatedDocumentIndexes($record->id, $documentIndexes);

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$form->clearValues();
			$this->redrawControl('conferenceYearAddForm');
		}

		$this->onAdd($record);
            };
            $form->onError[] = function($form) {
		$this->redrawControl('conferenceYearAddForm');
            };
            return $form;
	}

	public function createComponentConferenceYearEditForm(){
            if(!$this->isActionAllowed('edit')) return null;
            $form = new ConferenceYearForm(null,$this->loadPublishers(), $this->loadDocumentIndexes(), $this->conferenceModel, $this->conferenceYearModel, $this->conferenceYearIsIndexedModel, $this, 'conferenceYearEditForm');
            $this->reduceForm($form);
            $form->onSuccess[] = function(ConferenceYearForm $form) {

		$formValues = $form->getValuesTransformed();

		$formValues['submitter_id'] = $this->loggedUser->id;

		$documentIndexes = Func::getAndUnset($formValues, 'document_indexes');
		$this->sanitizeEntityData($formValues);

		$this->template->conferenceYearEdited = true;

		$this->conferenceYearModel->update($formValues);

		$record = $this->conferenceYearModel->findOneById($formValues['id']);

		$this->conferenceYearIsIndexedModel->setAssociatedDocumentIndexes($record->id, $documentIndexes);

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$form->setValues(array(), TRUE);
			$this->redrawControl('conferenceYearEditForm');
		}

		$this->onEdit($record);
            };


            $form->onError[] = function(ConferenceYearForm $form) {
                $this->redrawControl('conferenceYearEditForm');
            };
            return $form;
	}


	protected function sanitizeEntityData(&$data){
		Func::valOrNull($data,
			array('publisher_id', 'location', 'isbn', 'description', 'doi', 'issn', 'web', 'submitter_id', 'parent_id', 'name', 'abbreviation')
		);
	}

	public function handleShowPublisherInfoA($publisherId){
		if($publisherId) {
			$this->template->selectedPublisherIdA = $publisherId;
			$this->template->selectedPublisherInfoA = $this->publisherModel->findOneById($publisherId);
		}
		$this->redrawControl("publisherInfo-addnew");
	}

	public function handleShowPublisherInfoE($publisherId){
		if($publisherId) {
			$this->template->selectedPublisherIdE = $publisherId;
			$this->template->selectedPublisherInfoE = $this->publisherModel->findOneById($publisherId);
		}
		$this->redrawControl("publisherInfo-edit");
	}

	public function handleShowRelatedPublications($conferenceYearId){
		$this->template->relatedPublications = $this->publicationModel->findAllBy(array('conference_year_id' => $conferenceYearId));
		$this->redrawControl('relatedPublications');
	}

	public function handleDelete($id) {
		if(!$this->isActionAllowed('delete')) return;
		$conferenceYear = $this->conferenceYearModel->findOneById($id);

		if($conferenceYear) {

			$conferenceYear->toArray(); // load the object to be passed to the callback

			$this->conferenceYearModel->deleteAssociatedRecords($id);

			$this->template->conferenceYearDeleted = true;

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deleteConferenceYear');
			}

			$this->onDelete($conferenceYear);
		}
	}

	public function handleEdit($id) {
		if(!$this->isActionAllowed('edit')) return;

		$conferenceYear = $this->conferenceYearModel->findOneById($id);

		if($conferenceYear) {

			// load defaults
			$this["conferenceYearEditForm"]->setDefaults($conferenceYear); // set up new values

			// load document index defaults
			$indexedRecords = $this->conferenceYearIsIndexedModel->findAllByConferenceYearId($id);
			$documentIndexesIds = [];
			foreach($indexedRecords as $indexedRecord)
				$documentIndexesIds[] = $indexedRecord->ref('document_index')->id;

			$this["conferenceYearEditForm"]["document_indexes"]->setDefaultValue(
				$documentIndexesIds
			);


			if($conferenceYear["publisher_id"]) {
				$this->handleShowPublisherInfoE($conferenceYear["publisher_id"]);
			}

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('conferenceYearEditForm');
			}
		}
	}

	public function handleSetConferenceYearState($id, $state){
		if(!$this->isActionAllowed('archivedStateChange')) return null;

		if(!in_array($state, array('alive', 'archived'))) return;
		$this->conferenceYearModel->update(array(
			'id'        =>  $id,
			'state'     =>  $state
		));
		$this["controls"][$id]->redrawControl();
		$this->onArchivedStateChanged($id, $state);
	}

	public function handleFindConferenceYearsForTypeAhead($query, $conferenceYearId) {
		$records = $this->conferenceYearModel->findAllByKw($query);


		if($conferenceYearId) {
			$year = $this->conferenceYearModel->find($conferenceYearId)->w_year;
			if($year) $records->where('w_year = ?', $year);
		}

		$records = $records->order('w_year DESC')->limit(20);

		$toSend = array();
		foreach($records as $rec) {
			$toSend[] = array(
				'id'        =>  $rec->id,
				'name'      =>  $rec->name,
				'year'      =>  $rec->w_year,
				'from'      =>  $rec->w_from,
				'to'        =>  $rec->w_to,
				'location'  =>  $rec->location
			);
		}

		$this->presenter->sendResponse(new \Nette\Application\Responses\JsonResponse($toSend));
	}

	public function handleShowWorkshops($conferenceYearId) {
		if(!$this->isActionAllowed('showWorkshops')) return;
		$this->template->relatedWorkshops = $this->conferenceYearModel->findAllBy(array('parent_id' =>  $conferenceYearId));
		$this->template->conferenceYearId = $conferenceYearId;
		$this->redrawControl('relatedWorkshops');
	}

	public function handleAttachWorkshop($id, $conferenceYearId) {
		if(!$this->isActionAllowed('manageWorkshops')) return;
		$this->conferenceYearModel->find($id)->update(array('parent_id' => $conferenceYearId));
		$this->onWorkshopsUpdate($id, $conferenceYearId);
		$this->handleShowWorkshops($conferenceYearId);
	}

	public function handleDetachWorkshop($id, $conferenceYearId) {
		if(!$this->isActionAllowed('manageWorkshops')) return;
		$this->conferenceYearModel->find($id)->update(array('parent_id' => null));
		$this->onWorkshopsUpdate($id, $conferenceYearId);
		$this->handleShowWorkshops($conferenceYearId);
	}

}