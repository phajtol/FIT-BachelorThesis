<?php

namespace App\CrudComponents\Conference;

use App\Components\AcmCategoryList\AcmCategoryListComponent;
use App\Components\ButtonToggle\ButtonGroupComponent;
use App\Components\ConferenceCategoryList\ConferenceCategoryListComponent;
use App\Components\Publication\PublicationControl;
use App\CrudComponents\BaseCrudComponent;
use App\CrudComponents\BaseCrudControlsComponent;
use App\Forms\BaseForm;
use NasExt\Controls\SortingControl;
use Nette\Application\UI\Multiplier;
use Nette\Utils\DateTime;


class ConferenceCrud extends BaseCrudComponent {


	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var \App\Model\Conference */
	protected $conferenceModel;

	/** @var \App\Model\ConferenceYear */
	protected $conferenceYearModel;

	/** @var \App\Model\Publication */
	protected $publicationModel;

	/** @var \App\Model\Author */
	protected $authorModel;

	/** @var \App\Model\Publisher */
	protected $publisherModel;

	/** @var \App\Factories\IAcmCategoryListFactory */
	protected $acmCategoryListFactory;

	/** @var \App\Model\ConferenceHasAcmCategory */
	protected $conferenceHasAcmCategoryModel;

	/** @var \App\Factories\IConferenceCategoryListFactory */
	protected $conferenceCategoryListFactory;

	/** @var \App\Model\ConferenceHasCategory */
	protected $conferenceHasCategoryModel;

	/** @var  \App\Factories\IConferenceYearCrudFactory */
	protected $conferenceYearCrudFactory;

	/** @var  \App\Helpers\SortingControlFactory */
	protected $sortingControlFactory;

    /** @var array */
	public $onCreateConferenceYearCrud = [];

	/** @var array */
	public $onMergeConferences = [];

	/** @persistent */
	public $conferenceId = null;

	/** @var Callable[] event fired when conference is archived / brought back.. ($confId, bool $isArchived) */
	public $onConferenceArchived;


    /**
     * ConferenceCrud constructor.
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\Conference $conferenceModel
     * @param \App\Model\ConferenceYear $conferenceYearModel
     * @param \App\Model\Publication $publicationModel
     * @param \App\Model\Author $authorModel
     * @param \App\Model\Publisher $publisherModel
     * @param \App\Model\ConferenceHasAcmCategory $conferenceHasAcmCategoryModel
     * @param \App\Model\ConferenceHasCategory $conferenceHasCategoryModel
     * @param \App\Factories\IAcmCategoryListFactory $acmCategoryListFactory
     * @param \App\Factories\IConferenceCategoryListFactory $conferenceCategoryListFactory
     * @param \App\Factories\IConferenceYearCrudFactory $conferenceYearCrudFactory
     * @param \App\Helpers\SortingControlFactory $sortingControlFactory
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
		\Nette\Security\User $loggedUser,
        \App\Model\Conference $conferenceModel,
        \App\Model\ConferenceYear $conferenceYearModel,
        \App\Model\Publication $publicationModel,
        \App\Model\Author $authorModel,
        \App\Model\Publisher $publisherModel,
		\App\Model\ConferenceHasAcmCategory $conferenceHasAcmCategoryModel,
        \App\Model\ConferenceHasCategory $conferenceHasCategoryModel,
		\App\Factories\IAcmCategoryListFactory $acmCategoryListFactory,
        \App\Factories\IConferenceCategoryListFactory $conferenceCategoryListFactory,
		\App\Factories\IConferenceYearCrudFactory $conferenceYearCrudFactory,
        \App\Helpers\SortingControlFactory $sortingControlFactory ,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->conferenceModel = $conferenceModel;
		$this->conferenceYearModel = $conferenceYearModel;
		$this->publicationModel = $publicationModel;
		$this->authorModel = $authorModel;
		$this->publisherModel = $publisherModel;
		$this->conferenceHasAcmCategoryModel = $conferenceHasAcmCategoryModel;
		$this->conferenceHasCategoryModel = $conferenceHasCategoryModel;
		$this->conferenceYearCrudFactory = $conferenceYearCrudFactory;

		$this->acmCategoryListFactory = $acmCategoryListFactory;
		$this->conferenceCategoryListFactory = $conferenceCategoryListFactory;

		$this->sortingControlFactory = $sortingControlFactory;

		$this->loggedUser = $loggedUser;

		$this->onConferenceArchived = [];

		$this->writeActions[] = 'archivedStateChange';
		$this->writeActions[] = 'mergeConferences';

		$this->allowAction('archivedStateChange');
		$this->allowAction('showRelatedPublications');
		$this->allowAction('showConferenceYears');
		$this->allowAction('mergeConferences');

		$this->onControlsCreate[] = function (BaseCrudControlsComponent &$controlsComponent) use ($conferenceModel) {
			$controlsComponent->addActionAvailable('showConferenceYears');
			$controlsComponent->addActionAvailable('showRelatedPublications');
			$controlsComponent->addActionAvailable('mergeConferences');

			$conference = $conferenceModel->find($controlsComponent->getRecordId());

			if ($conference) {
				$conferenceState = $conference->state;

				$conferenceSTM = array(
					'alive'     =>  'dead',
					'dead'      =>  'alive'
				);

				$controlsComponent->addTemplateVars([
					'conferenceState' => $conferenceState,
					'nextConferenceState' => $conferenceSTM[$conferenceState],
					'setConferenceStateLink' => $this->link('setConferenceState!', array($controlsComponent->getRecordId(), $conferenceSTM[$conferenceState]))
				]);
			}
		};
	}

    /**
     * @return ButtonGroupComponent
     */
	protected function createComponentCPToggle(): ButtonGroupComponent
    {
		$c = parent::createComponentCPToggle();
		$c->onActiveButtonChanged[] = function () {
			$this->getPresenter()->redrawControl();
		};
		return $c;
	}

    /**
     * @return Multiplier
     */
	public function createComponentConferenceYear(): Multiplier
    {

		$parent = $this;

		return new Multiplier(function ($conferenceId) use ($parent) {
			/*
			$c = new \App\CrudComponents\ConferenceYear\ConferenceYearCrud(
				$conferenceId,
				$parent->loggedUser,
				$parent->publisherModel, $parent->publicationModel, $parent->conferenceYearModel, $parent->conferenceModel
			);*/

			$c = $parent->conferenceYearCrudFactory->create($conferenceId);

			$fnRedraw = function ($record) use ($parent, $conferenceId) {
				$this->loadConferenceYears();
				$parent->redrawControl('conferenceYearsShowAllRecords');
			};

			$c->onEdit[] = $fnRedraw;
			$c->onAdd[] = $fnRedraw;
			$c->onDelete[] = $fnRedraw;

			foreach($this->onCreateConferenceYearCrud as $callback) {
				call_user_func_array($callback, array(&$c));    // passing by reference
			}

			return $c;
		});

	}

    /**
     * @param string $name
     * @return ConferenceAddForm
     */
	public function createComponentConferenceAddForm(string $name): ConferenceAddForm
    {
        $form = new ConferenceAddForm($this->conferenceModel, $this, $name);
        $this->reduceForm($form);

        $form->onError[] = function () {
            $this->redrawControl('conferenceAddForm');
        };

        $form->onSuccess[] = function (ConferenceAddForm $form) {
		    $formValues = $form->getValuesTransformed();

		    $formValues['submitter_id'] = $this->loggedUser->id;

		    if (isset($form['acm_categories'])) {
			    $acm_categories = $formValues['acm_categories'] ? explode(" ", $formValues['acm_categories']) : [];
			    unset($formValues["acm_categories"]);
		    }

		    if (isset($form['conference_categories'])) {
			    $conference_categories = $formValues['conference_categories'] ? explode(' ', $formValues['conference_categories']) : [];
			    unset($formValues["conference_categories"]);
		    }

		    $record = $this->conferenceModel->insert($formValues);

		    if ($record) {
			    $this->template->conferenceAdded = true;

			    $cy = $this->conferenceYearModel->insert([
			        "w_year" => \date("Y"),
					"conference_id" => $record->id,
					"name" => $record->name,
					"abbreviation" => $record->abbreviation
                ]);

			    if (isset($acm_categories)) {
			        $this->conferenceHasAcmCategoryModel->setAssociatedAcmCategories($record->id, $acm_categories);
                }
			    if (isset($conference_categories)) {
			        $this->conferenceHasCategoryModel->setAssociatedConferenceCategories($record->id, $conference_categories);
                }

			    if ($this->presenter->isAjax()) {
				    $form->clearValues();
				    $this->redrawControl('conferenceAddForm');
			    } else {
			        $this->redirect('this');
                }

			    $this->onAdd($record, $cy);
		    }
        };

        return $form;
	}

    /**
     * @param string $name
     * @return ConferenceEditForm
     */
	public function createComponentConferenceEditForm(string $name): ConferenceEditForm
    {
        $form = new ConferenceEditForm($this, $name);
        $this->reduceForm($form);

        $form->onError[] = function () {
            $this->redrawControl('conferenceEditForm');
        };

        $form->onSuccess[] = function (ConferenceEditForm $form) {
		    $formValues = $form->getValuesTransformed();

		    $formValues['lastedit_submitter_id'] = $this->loggedUser->id;
		    $formValues['lastedit_timestamp'] = new DateTime();

		    if (isset($form['acm_categories'])) {
			    $acm_categories = $formValues['acm_categories'] ? explode(" ", $formValues['acm_categories']) : [];
			    unset($formValues["acm_categories"]);
		    }

		    if (isset($form['conference_categories'])) {
			    $conference_categories = $formValues['conference_categories'] ? explode(" ", $formValues['conference_categories']) : [];
			    unset($formValues["conference_categories"]);
		    }

		    $this->conferenceModel->update($formValues);
		    $record = $this->conferenceModel->find($formValues['id']);

		    $this->template->conferenceEdited = true;

		    if (isset($acm_categories)) {
		        $this->conferenceHasAcmCategoryModel->setAssociatedAcmCategories($record->id, $acm_categories);
            }
		    if (isset($conference_categories)) {
		        $this->conferenceHasCategoryModel->setAssociatedConferenceCategories($record->id, $conference_categories);
            }

		    if ($this->presenter->isAjax()) {
			    $this->redrawControl('conferenceEditForm');
		    } else {
		        $this->redirect('this');
            }

		    $this->onEdit($record);
        };

        return $form;
	}

    /**
     * @return BaseForm
     */
	public function createComponentMergeConferencesForm(): BaseForm
    {
		$form = new BaseForm();

		$form->addHidden('source_conference_id')
            ->addRule(\Nette\Forms\Form::INTEGER)
            ->setValue($this->conferenceId)
            ->setRequired(true);

		$form->addHidden('target_conference_id')
            ->addRule(\Nette\Forms\Form::INTEGER)
            ->setRequired(true);

		$form->addText('target_conference_name', 'Target conference'); // for typeahead
		$form->addSubmit('send', 'Move this conference');

		$form->setModal(true);
		$form->setAjax(true);

		$form->onSuccess[] = function (BaseForm $form) {
			$source_conference = $this->conferenceModel->find($form['source_conference_id']->getValue());
			$target_conference = $this->conferenceModel->find($form['target_conference_id']->getValue());

			if (!$source_conference) {
			    throw new \Exception('Source connference not found');
            }
			if (!$target_conference) {
			    throw new \Exception('Target connference not found');
            }

			$source_conference->toArray(); // load the object to be passed to the callback

			$this->conferenceModel->mergeConferences($source_conference->id, $target_conference->id);

			$this->onMergeConferences($source_conference, $target_conference);
			$this->redrawControl('conferencesMerged');
		};

		return $form;
	}

    /**
     * @return PublicationControl
     */
	public function createComponentPublication(): PublicationControl
    {
        return new PublicationControl();
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleDelete(int $id): void
    {
		$record = $this->conferenceModel->find($id);

		if($record) {
			$record->toArray(); // load the object to be passed to the callback

			$this->conferenceModel->deleteAssociatedRecords($record->id);

			$this->template->conferenceDeleted = true;

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deleteConference');
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
		$conference = $this->conferenceModel->find($id);

		// load acm categories
		$acm_category_ids = [];
		$acm_category_results = $this->conferenceHasAcmCategoryModel->findAllByConferenceId($id);

		foreach ($acm_category_results as $row) {
		    $acm_category_ids[] = $row['acm_category_id'];
        }

		if (isset($this['conferenceEditForm']['acm_categories'])) {
            $this['conferenceEditForm']['acm_categories']->setValue(implode(' ', $acm_category_ids));
        }

		// load conference categories
		$conference_category_ids = [];
		$conference_category_results = $this->conferenceHasCategoryModel->findAllByConferenceId($id);
		foreach ($conference_category_results as $row) {
		    $conference_category_ids[] = $row['conference_category_id'];
        }

		if (isset($this['conferenceEditForm']['conference_categories'])) {
            $this['conferenceEditForm']['conference_categories']->setValue(implode(' ', $conference_category_ids));
        }

		$this['conferenceEditForm']->setDefaults($conference); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('conferenceEditForm');
		}
	}

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleShowRelatedPublications(int $id): void
    {
        $conferenceYears = $this->conferenceYearModel->findAllBy(['conference_id' => $id])->order('w_year DESC');
        $publicationsByYears = [];
        $authorsByPubId = [];

        foreach ($conferenceYears as $year) {
            $publicationsByYears[$year->w_year] = $this->publicationModel->getMultiplePubInfoByParams(['conference_year_id' => $year->id]);

            foreach ($publicationsByYears[$year->w_year] as $pub) {
                $authorsByPubId[$pub->id] = $this->authorModel->getAuthorsNamesByPubIdPure($pub->id);
            }
        }

		$this->template->conferenceYears = $conferenceYears;
		$this->template->publicationsByYears = $publicationsByYears;
		$this->template->authorsByPubId = $authorsByPubId;

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('publicationsRelatedToConference');
		}
	}

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleShowConferenceYears(int $id): void
    {
		$this->conferenceId = $id;
		$this->loadConferenceYears();

		if($this->presenter->isAjax()) {
			$this->redrawControl('conferenceYearsBase');
			$this->redrawControl('conferenceYears');
		} else {
		    $this->redirect('this');
        }
	}

    /**
     * @param int $id
     * @param string $state
     */
	public function handleSetConferenceState(int $id, string $state): void
    {
		if (!in_array($state, ['alive', 'dead'])) {
		    return;
        }

		$this->conferenceModel->update([
			'id'        =>  $id,
			'state'     =>  $state
		]);

		$this->presenter->redrawControl('conferenceControls');
		$this->onConferenceArchived($id, $state);
	}

    /**
     * @param int $id
     */
	public function handleMergeConferences(int $id): void
    {
		if ($this->isActionAllowed('mergeConferences')) {
			$this->conferenceId = $id;
			$this->redrawControl('mergeConferencesForm');
		}
	}

    /**
     * @param $query
     * @throws \Nette\Application\AbortException
     */
	public function handleFindConferencesForTypeAhead($query): void
    {
		$records = $this->conferenceModel->findAllByKw($query);
		if ($this->conferenceId) {
		    $records = $records->where('id != ?', $this->conferenceId);
        }
		$records = $records->order('abbreviation ASC')->limit(20);
		$toSend = [];

		foreach($records as $rec) {
			$toSend[] = $rec->toArray();
		}

		$this->presenter->sendResponse(new \Nette\Application\Responses\JsonResponse($toSend));
	}

    /**
     *
     */
	protected function loadConferenceYears(): void
    {
		$this->template->conferenceYears = $this->conferenceYearModel->findAllByConferenceId($this->conferenceId)
			->order($this['cYSorting']->getColumn() . ' ' . $this['cYSorting']->getSortDirection());
		$this->template->conferenceId = $this->conferenceId;
	}

    /**
     * @param array|null $params
     */
	public function render(?array $params = []): void {

		$this->template->addFormAcmCategoriesElementId = isset($this['conferenceAddForm']['acm_categories'])
			? $this['conferenceAddForm']['acm_categories']->getHtmlId() : null;

		$this->template->editFormAcmCategoriesElementId = isset($this['conferenceEditForm']['acm_categories'])
			? $this['conferenceEditForm']['acm_categories']->getHtmlId() : null;

		$this->template->addFormConferenceCategoriesElementId = isset($this['conferenceAddForm']['conference_categories']) ?
			$this['conferenceAddForm']['conference_categories']->getHtmlId() : null;

		$this->template->editFormConferenceCategoriesElementId = isset($this['conferenceEditForm']['conference_categories']) ?
			$this['conferenceEditForm']['conference_categories']->getHtmlId() : null;

		$this->addDefaultTemplateVars([
			'conferenceAdded' => false,
			'conferenceEdited' => false,
			'conferenceDeleted' => false,
			'publicationsRelatedToConference' => [],
			'conferenceYears' => [],
			'conferenceId' => $this->conferenceId
		]);

		parent::render($params);
	}

    /**
     * @return AcmCategoryListComponent
     */
	public function createComponentAcmCategoryListA(): AcmCategoryListComponent
    {
		return $this->createAcmCategoryList();
	}

    /**
     * @return AcmCategoryListComponent
     */
	public function createComponentAcmCategoryListE(): AcmCategoryListComponent
    {
		return $this->createAcmCategoryList();
	}

    /**
     * @return AcmCategoryListComponent
     */
	protected function createAcmCategoryList(): AcmCategoryListComponent
    {
		$c = $this->acmCategoryListFactory->create();
		$c->setHeight(180);
		$c->setWidth(375);
		$c->setIsSelectable(true);
		$c->setHasControls(true);
		return $c;
	}

    /**
     * @return ConferenceCategoryListComponent
     */
	public function createComponentConferenceCategoryListA(): ConferenceCategoryListComponent
    {
		return $this->createConferenceCategoryList();
	}

    /**
     * @return ConferenceCategoryListComponent
     */
	public function createComponentConferenceCategoryListE(): ConferenceCategoryListComponent
    {
		return $this->createConferenceCategoryList();
	}

    /**
     * @return ConferenceCategoryListComponent
     */
	protected function createConferenceCategoryList(): ConferenceCategoryListComponent
    {
		$c = $this->conferenceCategoryListFactory->create();
		$c->setHeight(180);
		$c->setWidth(375);
		$c->setIsSelectable(true);
		$c->setHasControls(true);
		return $c;
	}

	/**
	 * @return \NasExt\Controls\SortingControl
	 */
	protected function createComponentCYSorting(): SortingControl
	{
		$tablePrefix = '';
		$control = $this->sortingControlFactory->create([
			'name' => $tablePrefix . 'name',
			'abbreviation' => $tablePrefix . 'abbreviation',
			'w_year' => $tablePrefix . 'w_year',
			'w_from' => $tablePrefix . 'w_from',
			'w_to' => $tablePrefix . 'w_to',
			'deadline' => $tablePrefix . 'deadline',
			'notification' => $tablePrefix . 'notification',
			'finalversion' => $tablePrefix . 'finalversion',
			'location' => $tablePrefix . 'location',
			'doi' => $tablePrefix . 'doi',
			'publisher_id' => $tablePrefix . 'publisher_id'
		],  'w_year', SortingControl::DESC);

		$control->setAjaxRequest(true);

		$control->onShort[] = function () {
			$this->loadConferenceYears();
			$this->redrawControl();
		};

		return $control;
	}

}
