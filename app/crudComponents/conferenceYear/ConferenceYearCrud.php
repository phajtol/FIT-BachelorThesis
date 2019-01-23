<?php

namespace App\CrudComponents\ConferenceYear;

use App\Components\StaticContentComponent;
use App\CrudComponents\BaseCrudComponent;
use App\CrudComponents\BaseCrudControlsComponent;
use App\CrudComponents\Publisher\PublisherCrud;
use App\Helpers\Func;
use Nette\Utils\DateTime;


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

	/** @var int */
	protected $conferenceId;

	/** @var array */
	public $onArchivedStateChanged = [];

	/** @var array */
	public $onWorkshopsUpdate = [];


    /**
     * ConferenceYearCrud constructor.
     * @param int $conferenceId
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\Publisher $publisherModel
     * @param \App\Model\Publication $publicationModel
     * @param \App\Model\ConferenceYear $conferenceYearModel
     * @param \App\Model\Conference $conferenceModel
     * @param \App\Model\DocumentIndex $documentIndexModel
     * @param \App\Model\ConferenceYearIsIndexed $conferenceYearIsIndexedModel
     * @param \App\Model\ConferenceYearIsbn $conferenceYearIsbnModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(int $conferenceId,
                                \Nette\Security\User $loggedUser,
                                \App\Model\Publisher $publisherModel,
								\App\Model\Publication $publicationModel,
                                \App\Model\ConferenceYear $conferenceYearModel,
                                \App\Model\Conference $conferenceModel,
								\App\Model\DocumentIndex $documentIndexModel,
                                \App\Model\ConferenceYearIsIndexed $conferenceYearIsIndexedModel,
								\App\Model\ConferenceYearIsbn $conferenceYearIsbnModel,
								\Nette\ComponentModel\IContainer $parent = NULL,
                                string $name = NULL)
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

		$this->onControlsCreate[] = function (BaseCrudControlsComponent &$controlsComponent) use ($conferenceYearModel) {
			$controlsComponent->addActionAvailable('showRelatedPublications');
			$controlsComponent->addActionAvailable('showWorkshops');

			$conferenceYear = $conferenceYearModel->find($controlsComponent->getRecordId());

			if($conferenceYear) {
				$conferenceYearState = $conferenceYear->state;

				$conferenceYearSTM = [
					'alive'     =>  'archived',
					'archived'  =>  'alive'
				];

				$controlsComponent->addTemplateVars([
					'conferenceYearState' => $conferenceYearState,
					'nextConferenceYearState' => $conferenceYearSTM[$conferenceYearState],
					'setConferenceYearStateLink' => $this->link('setConferenceYearState!', [$controlsComponent->getRecordId(), $conferenceYearSTM[$conferenceYearState]])
				]);
			}
		};
	}

    /**
     * @return int
     */
	public function getConferenceId(): int
    {
		return $this->conferenceId;
	}


	// post-construct
    /**
     * @param $presenter
     */
	protected function attached($presenter): void
    {
		parent::attached($presenter);

		// load forms
		if ($this->isActionAllowed('edit') || $this->isActionAllowed('add')) {
			$this->template->conferenceYearForm = $this['conferenceYearForm'];
		}

		// templates
		$this->template->conferenceYearAdded = false;
		$this->template->conferenceYearDeleted = false;
		$this->template->conferenceYearEdited = false;
		$this->template->relatedPublications = [];
		$this->template->relatedWorkshops = [];
	}

    /**
     * @return PublisherCrud
     */
	public function createComponentPublisherA(): PublisherCrud
    {
		$publisherCrud = new PublisherCrud(
		    $this->loggedUser, $this->publisherModel, $this->publicationModel, $this->conferenceYearModel, $this, 'publisherA'
		);

		$p = $this;

		$publisherCrud->onAdd[] = function ($record) use ($p) {
			$p->handleShowPublisherInfoA($record->id);
			$this->updatePublishers();
			$p['conferenceYearForm']['publisher_id']->setValue($record->id);
			$p->redrawControl('conferenceYearForm-publisher_id');
		};

		$publisherCrud->onDelete[] = function ($record) use ($p) {
			$p->handleShowPublisherInfoA($record->id);
			$this->updatePublishers();
			$p['conferenceYearForm']['publisher_id']->setValue(null);
			$p->redrawControl('conferenceYearForm-publisher_id');
		};

		$publisherCrud->onEdit[] = function ($record) use ($p) {
			$p->handleShowPublisherInfoA($record->id);
			$this->updatePublishers();
			$p['conferenceYearForm']['publisher_id']->setValue($record->id);
			//$p["conferenceYearForm"]->setValues(array('publisher_id', $record->id));
			$p->redrawControl('conferenceYearForm-publisher_id');
			//$p->redrawControl('conferenceYearForm-publisher_id');
		};

		return $publisherCrud;
	}

    /**
     * @return PublisherCrud
     */
	public function createComponentPublisherE(): PublisherCrud
    {
		$publisherCrud = new PublisherCrud(
			$this->loggedUser, $this->publisherModel, $this->publicationModel, $this->conferenceYearModel, $this, 'publisherE'
		);

		$p = $this;

		$publisherCrud->onAdd[] = function ($record) use ($p) {
			$p->handleShowPublisherInfoE($record->id);
			$this->updatePublishers();
			$p['conferenceYearForm']['publisher_id']->setValue($record->id);
			$p->redrawControl('conferenceYearForm-publisher_id');
		};

		$publisherCrud->onDelete[] = function ($record) use ($p) {
			$p->handleShowPublisherInfoE($record->id);
			$this->updatePublishers();
			$p['conferenceYearForm']['publisher_id']->setValue(null);
			$p->redrawControl('conferenceYearForm-publisher_id');
		};

		$publisherCrud->onEdit[] = function ($record) use ($p) {
			$p->handleShowPublisherInfoE($record->id);
			$this->updatePublishers();
			$p['conferenceYearForm']->setValues(['publisher_id', $record->id]);
			$p->redrawControl('conferenceYearForm-publisher_id');
		};

		return $publisherCrud;
	}

    /**
     * @return array
     */
	protected function loadPublishers(): array
    {
		$publisherSet = $this->publisherModel->findAll()->order('name ASC');
		$publishers = [];

		foreach ($publisherSet as $publisherRec) {
			$publishers[$publisherRec->id] = $publisherRec->name;
		}

		return $publishers;
	}

    /**
     * @return array
     */
	protected function loadDocumentIndexes(): array
    {
		$documentIndexSet = $this->documentIndexModel->findAll()->order('name ASC');
		$documentIndexes = [];

		foreach ($documentIndexSet as $documentIndex) {
			$documentIndexes[$documentIndex->id] = $documentIndex->name;
		}

		return $documentIndexes;
	}

    /**
     *
     */
	protected function updatePublishers(): void
    {
		$this['conferenceYearForm']->setPublishers($this->loadPublishers());
		$this->redrawControl('conferenceYearForm-publisher_id');
	}

    /**
     * @return ConferenceYearForm
     * @throws \Exception
     */
	public function createComponentConferenceYearForm(): ConferenceYearForm
    {
        if (!$this->isActionAllowed('edit') && !$this->isActionAllowed('add')) {
            return null;
        }

        $form = new ConferenceYearForm(
            $this->conferenceId,
            $this->loadPublishers(),
            $this->loadDocumentIndexes(),
            $this->conferenceModel,
            $this->conferenceYearModel,
            $this->conferenceYearIsIndexedModel,
            $this,
            'conferenceYearForm'
        );
        $this->reduceForm($form);

        $form->onSuccess[] = function (ConferenceYearForm $form) {
            $formValues = $form->getValuesTransformed();
            $documentIndexes = Func::getAndUnset($formValues, 'document_indexes');

    		$this->sanitizeEntityData($formValues);
    		unset($formValues['isbn']);
    		unset($formValues['isbn_count']);
    		unset($formValues['submitter_id']);

    		if (empty($formValues['id'])) {
    			$this->template->conferenceYearAdded = true;
    			unset($formValues['id']);
                $formValues['submitter_id'] = intval($this->loggedUser->id);
    			$record = $this->conferenceYearModel->insert($formValues);
    		} else {
    			unset($formValues['publisher_id']);
                $formValues['lastedit_submitter_id'] = intval($this->loggedUser->id);
                $formValues['lastedit_timestamp'] = new DateTime();
    			$this->template->conferenceYearEdited = true;
    			$this->conferenceYearModel->update($formValues);
    			$record = $this->conferenceYearModel->findOneById($formValues['id']);
    		}

    		$formValues = $form->getValuesTransformed();
    		$this->conferenceYearIsbnModel->findAllBy(["conference_year_id" => $record->id])->delete();

    		if (!empty($formValues['isbn'])) {
    			foreach ($formValues['isbn'] as $isbn) {
    				if (empty($isbn['isbn']) && empty($isbn['note']) ) {
    					continue;
    				}

    				$this->conferenceYearIsbnModel->insert([
    				    "conference_year_id" => $record->id,
    					"isbn" => $isbn['isbn'],
    					"type" => $isbn['type'],
    					"note" => $isbn['note']
                    ]);
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
                $this->redrawControl('conferenceYearDetail');
    		}
        };

        $form->onError[] = function (ConferenceYearForm $form) {
            $this->redrawControl('conferenceYearForm');
        };

        $this->template->getLatte()->addProvider('formsStack', [$form]);

        return $form;
	}

    /**
     * @param $data
     */
	protected function sanitizeEntityData(&$data): void
    {
		Func::valOrNull($data,
			['publisher_id', 'location', 'description', 'doi', 'web', 'submitter_id', 'parent_id', 'name', 'abbreviation']
		);
	}

    /**
     * @param int $publisherId
     */
	public function handleShowPublisherInfoA(int $publisherId): void
    {
		if ($publisherId) {
			$this->template->selectedPublisherIdA = $publisherId;
			$this->template->selectedPublisherInfoA = $this->publisherModel->findOneById($publisherId);
		}
		$this->redrawControl("publisherInfo-addnew");
	}

    /**
     * @param int $publisherId
     */
	public function handleShowPublisherInfoE(?int $publisherId): void
    {
		if($publisherId) {
			$this->template->selectedPublisherIdE = $publisherId;
			$this->template->selectedPublisherInfoE = $this->publisherModel->findOneById($publisherId);
		}
		$this->redrawControl('publisherInfo-edit');
	}

    /**
     * @param int $conferenceYearId
     */
	public function handleShowRelatedPublications(int $conferenceYearId): void
    {
		$this->template->relatedPublications = $this->publicationModel->findAllBy(['conference_year_id' => $conferenceYearId]);
		$this->redrawControl('relatedPublications');
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
		$conferenceYear = $this->conferenceYearModel->findOneById($id);

		if ($conferenceYear) {
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

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleEdit(int $id): void
    {
		if (!$this->isActionAllowed('edit')) {
		    return;
        }

		$conferenceYear = $this->conferenceYearModel->findOneById($id);

		if ($conferenceYear) {
			// load defaults
			$this['conferenceYearForm']->setDefaults($conferenceYear); // set up new values

			// load document index defaults
			$indexedRecords = $this->conferenceYearIsIndexedModel->findAllByConferenceYearId($id);
			$documentIndexesIds = [];

			foreach($indexedRecords as $indexedRecord) {
                $documentIndexesIds[] = $indexedRecord->ref('document_index')->id;
            }

			$this['conferenceYearForm']['document_indexes']->setDefaultValue($documentIndexesIds);

			if ($conferenceYear['publisher_id']) {
				$this->handleShowPublisherInfoE($conferenceYear['publisher_id']);
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

	public function handleAdd(): void
    {
		if (!$this->isActionAllowed('add')) {
		    return;
        }

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('conferenceYearForm');
		}
	}

    /**
     * @param int $id
     * @param string $state - 'alive' or 'archived'
     */
	public function handleSetConferenceYearState(int $id, string $state): void
    {
		if (!$this->isActionAllowed('archivedStateChange')) {
		    return;
        }
		if (!in_array($state, array('alive', 'archived'))) {
		    return;
        }
		$this->conferenceYearModel->update([
			'id'        =>  $id,
			'state'     =>  $state
		]);
		$this->onArchivedStateChanged($id, $state);

        $this->presenter->flashMessage('Operation was successfully completed.', 'alert-success');
        $this->presenter->redrawControl('conferenceYearShowAllRecords');
        $this->presenter->redrawControl('conferenceYearControls');
        $this->presenter->redrawControl('flashMessages');
	}

    /**
     * @param string $query
     * @param int $conferenceYearId
     * @throws \Nette\Application\AbortException
     */
	public function handleFindConferenceYearsForTypeAhead(?string $query, ?int $conferenceYearId): void
    {
		$records = $this->conferenceYearModel->findAllByKw($query);
		if ($conferenceYearId) {
			$year = $this->conferenceYearModel->find($conferenceYearId)->w_year;
			if ($year) {
			    $records->where('w_year = ?', $year);
            }
		}
		$records = $records->order('w_year DESC')->limit(20);

		$toSend = [];

		foreach($records as $rec) {
			$toSend[] = [
				'id'        =>  $rec->id,
				'name'      =>  $rec->name,
				'year'      =>  $rec->w_year,
				'from'      =>  $rec->w_from,
				'to'        =>  $rec->w_to,
				'location'  =>  $rec->location
			];
		}

		$this->presenter->sendResponse(new \Nette\Application\Responses\JsonResponse($toSend));
	}

    /**
     * @param int $conferenceYearId
     */
	public function handleShowWorkshops(int $conferenceYearId): void
    {
		if (!$this->isActionAllowed('showWorkshops')) {
		    return;
        }
		$this->template->relatedWorkshops = $this->conferenceYearModel->findAllBy(['parent_id' =>  $conferenceYearId]);
		$this->template->conferenceYearId = $conferenceYearId;
		$this->redrawControl('relatedWorkshops');
	}

    /**
     * @param int $id
     * @param int $conferenceYearId
     */
	public function handleAttachWorkshop(?int $id, ?int $conferenceYearId): void
    {
		if (!$this->isActionAllowed('manageWorkshops')) {
		    return;
        }

		$this->conferenceYearModel->find($id)->update(['parent_id' => $conferenceYearId]);
		$this->onWorkshopsUpdate($id, $conferenceYearId);
		$this->handleShowWorkshops($conferenceYearId);
	}

    /**
     * @param int $id
     * @param int $conferenceYearId
     */
	public function handleDetachWorkshop(int $id, int $conferenceYearId): void
    {
		if (!$this->isActionAllowed('manageWorkshops')) {
		    return;
        }

		$this->conferenceYearModel->find($id)->update(['parent_id' => null]);
		$this->onWorkshopsUpdate($id, $conferenceYearId);
		$this->handleShowWorkshops($conferenceYearId);
	}

    /**
     * @param int $count
     */
	public function handleAddIsbn(int $count): void
    {
		$this['conferenceYearForm']['isbn_count']->setValue($count);
		$this['conferenceYearForm']->addIsbn();

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
     * @param array|null $params
     */
	public function render(?array $params = []): void
    {
		parent::render($params);
	}

}
