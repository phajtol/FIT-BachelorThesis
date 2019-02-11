<?php

namespace App\Presenters;
use App\Components\AcmCategoryList\AcmCategoryListComponent;
use App\Components\AlphabetFilter\AlphabetFilterComponent;
use App\Components\ButtonToggle\ButtonGroupComponent;
use App\Components\ConferenceCategoryList\ConferenceCategoryListComponent;
use App\Components\Publication\PublicationControl;
use App\CrudComponents\Conference\ConferenceCrud;
use App\CrudComponents\ConferenceYear\ConferenceYearCrud;
use App\Forms\BaseForm;
use App\Model\Author;
use NasExt\Controls\SortingControl;
use Nette\Application\UI\Multiplier;

/**
 * @persistent(vp, alphabetFilter, conferenceYearStateFilter, conferenceIndividualFilter, sorting, sortingOthers, searchExtrasForm)
 */
class ConferencePresenter extends SecuredPresenter {

	/** @persistent */
	public $conferenceCategoryFilter;

	/** @persistent */
	public $acmCategoryFilter;

	/** @var  \App\Model\Conference @inject */
	public $conferenceModel;

	/** @var  \App\Model\ConferenceYear @inject */
	public $conferenceYearModel;

	/** @var \App\Factories\IConferenceYearCrudFactory @inject */
	public $conferenceYearCrudFactory;

	/** @var  \App\Factories\IFavouriteConferenceToggleFactory @inject */
	public $favouriteConferenceToggleFactory;

	/** @var  \App\Model\SubmitterFavouriteConference @inject */
	public $submitterFavouriteConferenceModel;

	/** @var  \App\Model\Submitter @inject */
	public $submitterModel;

	/** @var  \App\Model\ConferenceCategory @inject */
	public $conferenceCategoryModel;

	/** @var  \App\Model\AcmCategory @inject */
	public $acmCategoryModel;

	/** @var  \App\Model\Publication  @inject */
	public $publicationModel;

	/** @var  \App\Model\ConferenceYearIsIndexed @inject */
	public $conferenceYearIsIndexedModel;

	/** @var  \App\Factories\IConferenceCategoryListFactory @inject */
	public $conferenceCategoryListFactory;

	/** @var  \App\Factories\IAcmCategoryListFactory @inject */
	public $acmCategoryListFactory;

	/** @var  \App\Factories\IConferenceCrudFactory @inject */
	public $conferenceCrudFactory;

	/** @var int  */
	protected $currentConferenceId = 0;

	/** @var  */
	protected $currentConferenceYear;

	/** @var null  */
	protected $userFavouriteConferences = null;



    /**
     * ConferencePresenter constructor.
     */
    public function __construct()
    {
		$this->conferenceCategoryFilter = '';
		$this->acmCategoryFilter = '';
	}

    /**
     * @param string $name
     * @return AlphabetFilterComponent
     * @throws \ReflectionException
     */
	public function createComponentAlphabetFilter(string $name): AlphabetFilterComponent
    {
		$c = new AlphabetFilterComponent($this, $name);

		$c->setAjaxRequest(false);/*->onFilter[] = function($filter) use ($name) {
			if ($this->isAjax()) $this->redrawControl('conferenceYearsShowAll');
		};*/
		$c->onFilter[] = function () {
			$this->resetPagination();
		};

		return $c;
	}

    /**
     * @return ButtonGroupComponent
     */
	public function createComponentConferenceYearStateFilter(): ButtonGroupComponent
    {
		$c = new ButtonGroupComponent([
			'alive'     =>  [
				'caption'   =>  'Alive',
				'icon'      =>  'tree-deciduous'
			],
			[
				'caption'   =>  'Archived',
				'icon'      =>  'eye-close',
				'items'     =>  [
					'archived-last' =>  [
						'caption'   =>  'Archived - last conference years only'
					],
					'archived'      =>  [
						'caption'   =>  'Archived',
						//'icon'      =>  'eye-close'
					]
				]
			],
			'all'      =>  [
				'caption'   =>  'All',
            ]
		], 'alive');

		$c->onActiveButtonChanged[] = function () {
			$this->resetPagination();
		};

		return $c;
	}

    /**
     * @return ButtonGroupComponent
     */
	public function createComponentConferenceIndividualFilter(): ButtonGroupComponent
    {
		$c = new ButtonGroupComponent([
			'all'     =>  [
				'caption'   =>  'All'
			],
			'starred'      =>  [
				'caption'   =>  'Starred',
				'icon'      =>  'star'
			],
			'suggested'      =>  [
				'caption'   =>  'Suggested',
				'icon'      =>  'bell'
			]
		], 'all');

		$c->onActiveButtonChanged[] = function () {
			$this->resetPagination();
		};

		return $c;
	}

    /**
     * @return mixed
     */
	protected function getConferenceIndividualFilter()
    {
		return $this['conferenceIndividualFilter']->getActiveButtonName();
	}

    /**
     * @return mixed
     */
	protected function getConferenceYearStateFilter()
    {
		return $this['conferenceYearStateFilter']->getActiveButtonName();
	}

    /**
     * @param null $keywords
     * @throws \Exception
     */
	public function renderShowAll($keywords = null): void
    {
		if (!$this->template->records) {    // can be loaded only single one in case of edit
			if ($keywords !== null) {
				$this["searchForm"]->setDefaults(['keywords' => $keywords]);
				$this->records = $this->conferenceYearModel->findAllByKw($keywords);
			} else {
				$this->records = $this->conferenceYearModel->findAll();
			}
			/** @var $sorting \NasExt\Controls\SortingControl */
			$sorting = $this["sorting"];

			/** @var $alphabetFilter \App\Components\AlphabetFilter\AlphabetFilterComponent */
			$alphabetFilter = $this["alphabetFilter"];

			/** @var $conferenceYearStateFilter \App\Components\ButtonToggle\ButtonGroupComponent */
			$conferenceYearStateFilter = $this["conferenceYearStateFilter"];

			/** @var $conferenceIndividualFilter \App\Components\ButtonToggle\ButtonGroupComponent */
			$conferenceIndividualFilter = $this["conferenceIndividualFilter"];

			// filter by name & abbr vs abbr
			//if($alphabetFilter->getFilter()) $this->records->where('(conference_year.name LIKE ? OR conference_year.abbreviation LIKE ? OR conference.name LIKE ? OR conference.abbreviation LIKE ?)', $alphabetFilter->getFilter() . '%', strtolower($alphabetFilter->getFilter()) . "%", $alphabetFilter->getFilter() . '%', strtolower($alphabetFilter->getFilter()) . "%");
			if ($alphabetFilter->getFilter()) {
			    $this->records->where('(conference_year.abbreviation LIKE ? OR conference.abbreviation LIKE ?)', $alphabetFilter->getFilter() . '%', strtolower($alphabetFilter->getFilter()) . "%");
            }

			if ($this->acmCategoryFilter) {
				$acmCategoriesIds = $this->acmCategoryModel->getAllSubtreeIds(explode(" ", $this->acmCategoryFilter));
				$this->records->where('conference:conference_has_acm_category.acm_category_id IN ?', $acmCategoriesIds);
			}

			if ($this->conferenceCategoryFilter) {
				$conferenceCategoriesIds = $this->conferenceCategoryModel->getAllSubtreeIds(explode(" ", $this->conferenceCategoryFilter));
				$this->records->where('conference:conference_has_category.conference_category_id IN ?', $conferenceCategoriesIds);
			}

			switch ($conferenceIndividualFilter->getActiveButtonName()) {
				case 'starred':
					$this->records->where('conference:submitter_favourite_conference.submitter_id = ?', $this->getUser()->id);
					break;

				case 'suggested':
					// load user cu groups
					$userSuggestedCategoriesIds = $this->submitterModel->getAllUserSuggestedConferenceCategoriesIds($this->getUser()->id);
					// load all subtree items
					$userSuggestedCategoriesIds = $this->conferenceCategoryModel->getAllSubtreeIds($userSuggestedCategoriesIds);
					$this->records->where('conference:conference_has_category.conference_category_id IN ?', $userSuggestedCategoriesIds);
					break;
			}

			if ($conferenceYearStateFilter->getActiveButtonName() != 'all') {
                $this->records->where('conference_year.state = ?',
                    str_replace('archived-last', 'archived', $conferenceYearStateFilter->getActiveButtonName()));
            }

			$this->records->where('conference.state = ?', 'alive' );

			if ($conferenceYearStateFilter->getActiveButtonName() == 'archived-last') {
				$this->records = $this->conferenceYearModel->findOnlyLastYears(
					$this->records
				);
			}

			$this->setupRecordsPaginator();
			$this->records->order($sorting->getColumn() . ' ' . $sorting->getSortDirection());

			// add secondary sorting
			list($sortTable, $sortCol) = explode('.', $sorting->getColumn());
			if(in_array($sortCol, array('abbreviation', 'name'))) $this->records->order('w_year DESC');

			$this->template->records = $this->records;
		}

		$this->template->uniqid = $this->getUniqueId();
		$this->template->conferenceCategoriesInputId = $this['searchExtrasForm']['conference_categories']->getHtmlId();
		$this->template->acmCategoriesInputId = $this['searchExtrasForm']['acm_categories']->getHtmlId();

		$this->template->extraSearchApplied = false;
		foreach ($this['searchExtrasForm']->getValues() as $k => $val) {
		    if ($val) {
		        $this->template->extraSearchApplied = true;
		        break;
		    }
        }

		$this->template->deadlineNotificationThreshold = (new \Nette\Utils\DateTime())->add(new \DateInterval('P' . $this->userSettings->deadline_notification_advance . 'D'));
		$this->template->now = new \Nette\Utils\DateTime();
	}

    /**
     * @param int $id
     */
	public function actionShow(int $id): void
    {
		// load conference year
		$conferenceYear = $this->conferenceYearModel->find($id);

		if (!$conferenceYear) {
			$this->errorFlashMessage('Given conference year does not exist!');
			return;
		}

		$this->currentConferenceId = $conferenceYear->conference_id;
		$this->currentConferenceYear = $conferenceYear;
	}

    /**
     * @throws \Exception
     */
	public function renderShow(): void
    {

		if (!$this->currentConferenceYear) {
		    return;
        }

		$conferenceYear = $this->currentConferenceYear;

		$sorting = $this["sortingOthers"];
		/** @var $sorting \NasExt\Controls\SortingControl */

		// load other conference years
		$otherConferenceYears = $this->conferenceYearModel->findAllBy(["conference_id"   =>  $conferenceYear->conference_id])
            ->order($sorting->getColumn() . ' ' . $sorting->getSortDirection());


		// load associated publications
		$allConferenceYearsIds = [$conferenceYear->id];
		foreach ($otherConferenceYears as $iConferenceYear) {
			$allConferenceYearsIds[] = $iConferenceYear->id;
		}

		// load associated publications
		$associtatedPublicationsByConferenceYear = [];
		$publications = $this->publicationModel->getPublicationsByConferenceYears($allConferenceYearsIds);

			// load conference years to the apbcy array
			$associatedPublicationsByConferenceYear[$conferenceYear->id] = array_merge($conferenceYear->toArray(),
				[
					'current'       =>  true,
					'publications'  =>  []
				]
			);

			foreach ($otherConferenceYears as $iConferenceYear) {
				$associatedPublicationsByConferenceYear[$iConferenceYear->id] = array_merge($iConferenceYear->toArray(),
					[
						'current'       =>  false,
						'publications'  =>  []
					]
				);
			}

			// associate publications
			foreach ($publications as $publication) {
				$associatedPublicationsByConferenceYear[$publication->conference_year_id]['publications'][] = $publication;
			}

			// load authors per publications
			$authorsOfPublications = [];
			foreach ($publications as $publication) {
				$authorsOfPublications[$publication->id] = [];
				foreach ($publication->related('author_has_publication') as $ahp) {
					$authorsOfPublications[$publication->id][] = $ahp->ref('author');
				}
			}

		// load indexed
		$conferenceYearIsIndexed = $this->conferenceYearIsIndexedModel->getAssociatedDocumentIndexes($conferenceYear->id);

		// load workshops
		$this->loadWorkshops($conferenceYear->id);

		$this->template->conferenceYear = $conferenceYear;
		$this->template->otherConferenceYears = $otherConferenceYears;
		$this->template->associatedPublicationsByConferenceYear = $associatedPublicationsByConferenceYear;
		$this->template->authorsOfPublications = $authorsOfPublications;
		$this->template->conferenceYearIsIndexed = $conferenceYearIsIndexed;
        $this->template->conference = $this->conferenceModel->getConferenceByConferenceYearId($conferenceYear->conference_id);

		// load categories
		$this->template->acmCategories = [];
		foreach ($conferenceYear->ref('conference')->related('conference_has_acm_category') as $chac) {
            $this->template->acmCategories[] = $chac->ref('acm_category');
        }

		$this->template->conferenceCategories = [];
		foreach ($conferenceYear->ref('conference')->related('conference_has_category') as $chc) {
            $this->template->conferenceCategories[] = $chc->ref('conference_category');
        }

		// load publisher
		$this->template->publisher = ($pub = $conferenceYear->ref('publisher')) ? $pub : null;

		// load workshop
		$this->template->parentConferenceYear = (!$conferenceYear->parent_id ? null :
			$this->conferenceYearModel->find($conferenceYear->parent_id));

		$this->template->deadlineNotificationThreshold = (new \Nette\Utils\DateTime())->add(new \DateInterval('P' . $this->userSettings->deadline_notification_advance . 'D'));
		$this->template->now = new \Nette\Utils\DateTime();
	}

    /**
     * @param int $conferenceYearId
     */
	protected function loadWorkshops(int $conferenceYearId): void
    {
		$associatedWorkshops = $this->conferenceYearModel->findAllBy(['parent_id' => $conferenceYearId])->order('name ASC');
		$this->template->associatedWorkshops = $associatedWorkshops;
	}


	/**
	 * @return \NasExt\Controls\SortingControl
	 */
	protected function createComponentSorting(): SortingControl
	{
		$tablePrefix = $this->conferenceYearModel->getTableName() . ".";
		$control = $this->sortingControlFactory->create([
			'name' => $tablePrefix . 'name',
			'abbreviation' => $tablePrefix . 'abbreviation',
			'w_year' => $tablePrefix . 'w_year',
			'w_from_to' => $tablePrefix . 'w_from',
			//'w_to' => $tablePrefix . 'w_to',
			'deadline' => $tablePrefix . 'deadline',
			'notification' => $tablePrefix . 'notification',
			'finalversion' => $tablePrefix . 'finalversion',
			'location' => $tablePrefix . 'location'
        ],  'abbreviation', SortingControl::ASC);

		return $control;
	}

	/**
	 * @return \NasExt\Controls\SortingControl
	 */
	protected function createComponentSortingOthers(): SortingControl
	{
		$tablePrefix = $this->conferenceYearModel->getTableName() . ".";

		$control = $this->sortingControlFactory->create([
			'name' => $tablePrefix . 'name',
			'abbreviation' => $tablePrefix . 'abbreviation',
			'w_year' => $tablePrefix . 'w_year',
			'w_from' => $tablePrefix . 'w_from',
			'w_to' => $tablePrefix . 'w_to',
			'deadline' => $tablePrefix . 'deadline',
			'location' => $tablePrefix . 'location'
		],  'w_year', SortingControl::DESC);

		$control->setAjaxRequest(true);
		$control->onShort[] = function () {
			$this->redrawControl('otherConferenceYears');
		};

		return $control;
	}

    /**
     * @return \App\CrudComponents\Conference\ConferenceCrud
     */
	protected function createComponentConferenceCrud(): ConferenceCrud
    {
		$c = $this->conferenceCrudFactory->create();

		if (!$this->getUser()->isAllowed('ConferenceAdmin', 'showall')) {
			$c->setReadOnly(true);
		}

		$c->onAdd[] = function ($row, $cy) {
			$this->template->newConferenceId = $row->id;
			$this['particularCYCrud'][$row->id]->handleEdit($cy->id);
			$this->redrawControl('showAddCY');
			$this->redrawControl('conferenceYearsShowAll');
		};

		$c->onDelete[] = function () {
		    $this->successFlashMessage('The whole conference has been deleted successfully.');
		    $this->redirect('Conference:showall');
		};

		$c->onEdit[] = function () {
		    $this->successFlashMessage('The whole conference has been edited successfully.');
		    $this->redrawControl('conferenceYearDetail');
		};

		$c->onMergeConferences[] = function () {
			$this->successFlashMessage('The whole conference has been merged with another one successfully');
			$this->redirect('this');
		};

		return $c;
	}

    /**
     * @param int|null $conferenceId
     * @return \App\CrudComponents\ConferenceYear\ConferenceYearCrud
     */
	private function createComponentCYCrud(?int $conferenceId = null): ConferenceYearCrud
    {
		$c = $this->conferenceYearCrudFactory->create(is_null($conferenceId) ? $this->currentConferenceId : $conferenceId);

		// logged user has access to conference administration
		if ($this->getUser()->isAllowed('ConferenceAdmin', 'showall')) {
			/*if(!$this->currentConferenceId)
				$c->disallowAction('add');*/
		} else {
			$c->setReadOnly(true);
			if ($this->isPU()) {
				$c->allowAction('showRelatedPublications');
			} else {
				$c->disallowAction('showRelatedPublications');
			}
		}

		return $c;
	}

    /**
     * @return Multiplier
     */
	protected function createComponentParticularCYCrud(): Multiplier
    {
		return new Multiplier(function (int $conferenceId) {
			$c = $this->createComponentCYCrud($conferenceId);

			$c->onAdd[] = function ($row) {
				$this->successFlashMessage('The new year of conference was added successfully');
				$this->redirect('Conference:show', $row->id);
			};

			return $c;
		});
	}

    /**
     * @return ConferenceYearCrud
     */
	protected function createComponentCrud(): ConferenceYearCrud
    {
		$c = $this->createComponentCYCrud();

		$c->onDelete[] = function ($row) {
			$this->redirect('Conference:showall');
			$this->successFlashMessage('The year of conference was deleted successfully.');
		};

		$c->onEdit[] = function ($row) {
			$this->actionShow($row->id);
			$this->redrawControl('conferenceYearDetail');
			$this->successFlashMessage('The year of conference was edited successfully.');
		};

		$c->onWorkshopsUpdate[] = function ($id, $conferenceYearId) {
			$this->loadWorkshops($conferenceYearId);
			$this->redrawControl('workshopsShowAllRecords');
		};

		return $c;
	}

    /**
     * @return ConferenceYearCrud
     */
	protected function createComponentCrudOthers(): ConferenceYearCrud
    {
		$c = $this->createComponentCYCrud();

		$c->onDelete[] = function ($row) {
			$this->redrawControl('conferenceYearsShowAll');
			$this->redrawControl('otherConferenceYears');
			$this->successFlashMessage('The year of conference was deleted successfully.');
		};
		$c->onEdit[] = function ($row) {
			$this->redrawControl('conferenceYearsShowAll');
			$this->redrawControl('otherConferenceYears');
		};

		$c->onAdd[] = function ($row) {
			$this->redrawControl('otherConferenceYears');
			$this->successFlashMessage('The new year of conference was added successfully.');
			$this->redirect('Conference:show', $row->id);
		};

		return $c;
	}

    /**
     * @return ConferenceYearCrud
     */
	protected function createComponentCrudList(): ConferenceYearCrud
    {
		$c = $this->createComponentCYCrud();

		$c->onDelete[] = function ($row) {
			$this->successFlashMessage('The year of conference was deleted successfully.');
			$this->redrawControl('conferenceYearsShowAll');
		};
		$c->onEdit[] = function ($row) {
			$this->redrawControl('conferenceYearsShowAll');
			$this->successFlashMessage('The year of conference was edited successfully.');
		};

		return $c;
	}

    /**
     * @return Multiplier
     */
	protected function createComponentFavouriteToggle(): Multiplier
    {
		return new Multiplier(function (int $conferenceId) {
			$c = $this->favouriteConferenceToggleFactory->create($this->getUser()->id, $conferenceId);

			$c->setIsFavourite(in_array($conferenceId, $this->getUserFavouriteConferences()));

			$c->setAjaxRequest(true);

			$c->onMarkedAsFavourite[] = function () {
                $this->presenter->flashMessage('Operation was successfully completed.', 'alert-success');
                $this->redrawControl('conferenceYearsShowAllRecords');
                $this->redrawControl('conferenceControls');
				$this->redrawControl('flashMessages');
			};

			return $c;
		});
	}

    /**
     * @return array|null
     */
	protected function getUserFavouriteConferences(): ?array
    {
		// if user favourite conferences are not cached, cache it now
		if ($this->userFavouriteConferences === null) {
			$this->userFavouriteConferences =
				$this->submitterFavouriteConferenceModel->getUserFavouriteConferencesIds($this->getUser()->id);
		}

		return $this->userFavouriteConferences;
	}

    /**
     * @return \App\Components\ConferenceCategoryList\ConferenceCategoryListComponent
     */
	protected function createComponentConferenceCategoryList(): ConferenceCategoryListComponent
    {
		$c = $this->conferenceCategoryListFactory->create();
		$this->setupCategoryList($c);
		return $c;
	}

    /**
     * @return \App\Components\AcmCategoryList\AcmCategoryListComponent
     */
	protected function createComponentAcmCategoryList(): AcmCategoryListComponent
    {
		$c = $this->acmCategoryListFactory->create();
		$this->setupCategoryList($c);
		return $c;
	}

    /**
     * @param \App\Components\CategoryList\CategoryListComponent $c
     */
	protected function setupCategoryList(\App\Components\CategoryList\CategoryListComponent &$c): void
    {
		$c->setHasControls(false);
		$c->setHasThreeStates(true);
		$c->setHasDnD(false);
		$c->setHeight(200);
		$c->setWidth(270);
		$c->setIsSelectable(true);
	}

    /**
     * @return \App\Forms\BaseForm
     */
	protected function createComponentSearchExtrasForm(): BaseForm
    {
		$form = new BaseForm();

		$conferenceCategoriesInput = $form->addText('conference_categories', 'Conference categories')
			->addRule(\PublicationFormRules::CATEGORIES, 'Valid categories list is required')
            ->setRequired(false)
			->setValue($this->conferenceCategoryFilter ? $this->conferenceCategoryFilter : '');

		$acmCategoriesInput = $form->addText('acm_categories', 'ACM categories')
			->addRule(\PublicationFormRules::CATEGORIES, 'Valid categories list is required')
            ->setRequired(false)
			->setValue($this->acmCategoryFilter ? $this->acmCategoryFilter : '');

		// todo later
		// $operator = $form->addRadioList('operator', 'Search operator', array('OR' => 'OR', 'AND' => 'AND'))->setDefaultValue('AND');

		$form->addSubmit('submit', 'Apply');

		$form->onError[] = function () {
		    $this->redrawControl('searchExtrasForm');
		};

		$form->onSuccess[] = function (BaseForm $form) {
			$values = $form->getValuesTransformed();
			$this->conferenceCategoryFilter = $values->conference_categories;
			$this->acmCategoryFilter = $values->acm_categories;
			$this->resetPagination();
		};

		return $form;
	}


    /**
     * @return PublicationControl
     */
	protected function createComponentPublication(): PublicationControl
    {
        return new PublicationControl();
    }

}
