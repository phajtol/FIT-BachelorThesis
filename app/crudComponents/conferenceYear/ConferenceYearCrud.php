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

	/** @var  \App\Model\ConferenceYearIsbn */
	protected $conferenceYearIsbnModel;

	/** @var  \Nette\Security\User */
	protected $loggedUser;

	protected $conferenceId;

	public $onArchivedStateChanged = [];

	public $onWorkshopsUpdate = [];

	public function __construct($conferenceId, \Nette\Security\User $loggedUser, \App\Model\Publisher $publisherModel,
								\App\Model\Publication $publicationModel, \App\Model\ConferenceYear $conferenceYearModel, \App\Model\Conference $conferenceModel,
								\App\Model\DocumentIndex $documentIndexModel, \App\Model\ConferenceYearIsIndexed $conferenceYearIsIndexedModel,
								\App\Model\ConferenceYearIsbn $conferenceYearIsbnModel,
								\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		$this->conferenceYearModel = $conferenceYearModel;
		$this->conferenceModel = $conferenceModel;
		$this->publicationModel = $publicationModel;
		$this->publisherModel = $publisherModel;
		$this->documentIndexModel = $documentIndexModel;
		$this->conferenceYearIsIndexedModel = $conferenceYearIsIndexedModel;
		$this->conferenceYearIsbnModel = $conferenceYearIsbnModel;

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
		if($this->isActionAllowed('edit') || $this->isActionAllowed('add')) {
			$this->template->conferenceYearForm = $this['conferenceYearForm'];
		}

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
			$p["conferenceYearForm"]["publisher_id"]->setValue($record->id);
			$p->redrawControl('conferenceYearForm-publisher_id');
		};

		$publisherCrud->onDelete[] = function($record) use ($p) {
			$p->handleShowPublisherInfoA($record->id);
			$this->updatePublishers();
			$p["conferenceYearForm"]["publisher_id"]->setValue(null);
			$p->redrawControl('conferenceYearForm-publisher_id');
		};

		$publisherCrud->onEdit[] = function($record) use ($p) {
			$p->handleShowPublisherInfoA($record->id);
			$this->updatePublishers();
			$p["conferenceYearForm"]["publisher_id"]->setValue($record->id);
			//$p["conferenceYearForm"]->setValues(array('publisher_id', $record->id));
			$p->redrawControl('conferenceYearForm-publisher_id');
			//$p->redrawControl('conferenceYearForm-publisher_id');
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
			$p["conferenceYearForm"]["publisher_id"]->setValue($record->id);
			$p->redrawControl('conferenceYearForm-publisher_id');
		};

		$publisherCrud->onDelete[] = function($record) use ($p) {
			$p->handleShowPublisherInfoE($record->id);
			$this->updatePublishers();
			$p["conferenceYearForm"]["publisher_id"]->setValue(null);
			$p->redrawControl('conferenceYearForm-publisher_id');
		};

		$publisherCrud->onEdit[] = function($record) use ($p) {
			$p->handleShowPublisherInfoE($record->id);
			$this->updatePublishers();
			$p["conferenceYearForm"]->setValues(array('publisher_id', $record->id));
			$p->redrawControl('conferenceYearForm-publisher_id');
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
		$this['conferenceYearForm']->setPublishers($this->loadPublishers());
		$this->redrawControl('conferenceYearForm-publisher_id');
	}

	public function createComponentConferenceYearForm(){
    if(!$this->isActionAllowed('edit') && !$this->isActionAllowed('add')) return null;
    $form = new ConferenceYearForm($this->conferenceId,$this->loadPublishers(), $this->loadDocumentIndexes(), $this->conferenceModel, $this->conferenceYearModel, $this->conferenceYearIsIndexedModel, $this, 'conferenceYearForm');
    $this->reduceForm($form);
    $form->onSuccess[] = function(ConferenceYearForm $form) {
    		$formValues = $form->getValuesTransformed();

    		$formValues['submitter_id'] = intval($this->loggedUser->id);

    		$documentIndexes = Func::getAndUnset($formValues, 'document_indexes');

    		$this->sanitizeEntityData($formValues);
    		unset($formValues['isbn']);
    		unset($formValues['isbn_count']);

    		if (empty($formValues['id'])) {
    			$this->template->conferenceYearAdded = true;
    			$record = $this->conferenceYearModel->insert($formValues);
    		} else {
    			unset($formValues['publisher_id']);
    			$this->template->conferenceYearEdited = true;
    			$this->conferenceYearModel->update($formValues);
    			$record = $this->conferenceYearModel->findOneById($formValues['id']);
    		}

    		$formValues = $form->getValuesTransformed();

    		$this->conferenceYearIsbnModel->findAllBy(["conference_year_id" => $record->id])
    											->delete();

    		if (!empty($formValues['isbn'])) {
    			foreach ($formValues['isbn'] as $isbn) {
    				if (empty($isbn['isbn']) && empty($isbn['note']) ) {
    					continue;
    				}
    				$this->conferenceYearIsbnModel->insert(["conference_year_id" => $record->id,
    																	"isbn" => $isbn['isbn'],
    																	"type" => $isbn['type'],
    																	"note" => $isbn['note']]);
    			}
    		}


    		if (empty($record->id)) {
    			$this->onAdd($record);
    		} else {
    			$this->onEdit($record);
    		}


    		$this->conferenceYearIsIndexedModel->setAssociatedDocumentIndexes($record->id, $documentIndexes);

    		if (!$this->presenter->isAjax()) {
    			$this->presenter->redirect('this');
    		} else {
    			$this->redrawControl('conferenceYearForm');
    		}
    };
    $form->onError[] = function(ConferenceYearForm $form) {
        $this->redrawControl('conferenceYearForm');
    };
    return $form;
	}


	protected function sanitizeEntityData(&$data){
		Func::valOrNull($data,
			array('publisher_id', 'location', 'description', 'doi', 'web', 'submitter_id', 'parent_id', 'name', 'abbreviation')
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
			$this["conferenceYearForm"]->setDefaults($conferenceYear); // set up new values

			// load document index defaults
			$indexedRecords = $this->conferenceYearIsIndexedModel->findAllByConferenceYearId($id);
			$documentIndexesIds = [];
			foreach($indexedRecords as $indexedRecord)
				$documentIndexesIds[] = $indexedRecord->ref('document_index')->id;

			$this["conferenceYearForm"]["document_indexes"]->setDefaultValue(
				$documentIndexesIds
			);


			if($conferenceYear["publisher_id"]) {
				$this->handleShowPublisherInfoE($conferenceYear["publisher_id"]);
			}

			$cont = $this['conferenceYearForm']['isbn'];
			$isbn = $this->conferenceYearIsbnModel->findAllBy(["conference_year_id" => $conferenceYear->id]);
			$i = 0;
			$count = count($isbn);
			$this['conferenceYearForm']->setIsbnCount($count);

			$this['conferenceYearForm']->addIsbn();

			foreach ($isbn as $row) {
				$cont[$i]['isbn']->setDefaultValue($row['isbn']);
				$cont[$i]['type']->setDefaultValue($row['type']);
				$cont[$i]['note']->setDefaultValue($row['note']);
				$i++;
			}

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('conferenceYearForm');
			}
		}
	}

	public function handleAdd() {
		if(!$this->isActionAllowed('add')) return;

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('conferenceYearForm');
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

	public function handleAddIsbn($count) {
		$this['conferenceYearForm']['isbn_count']->setValue($count);
		$this['conferenceYearForm']->addIsbn();

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

		parent::render();
	}

}
