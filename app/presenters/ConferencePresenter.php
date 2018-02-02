<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 7.4.2015
 * Time: 16:48
 */

namespace App\Presenters;
use Nette\Application\UI\Multiplier;

/**
 * @persistent(vp, alphabetFilter, conferenceYearStateFilter, conferenceIndividualFilter, sorting, sortingOthers, searchExtrasForm)
 */
class ConferencePresenter extends SecuredPresenter {

	/** @persistent */
	public $conferenceCategoryFilter;

	/** @persistent */
	public $acmCategoryFilter;

	/** @var  \App\Model\Conference */
	protected $conferenceModel;

	/** @var  \App\Model\ConferenceYear */
	protected $conferenceYearModel;

	/** @var \App\Factories\IConferenceYearCrudFactory */
	protected $conferenceYearCrudFactory;

	/** @var  \App\Factories\IFavouriteConferenceToggleFactory */
	protected $favouriteConferenceToggleFactory;

	/** @var  \App\Model\SubmitterFavouriteConference */
	protected $submitterFavouriteConferenceModel;

	/** @var  \App\Model\Submitter */
	protected $submitterModel;

	/** @var  \App\Model\ConferenceCategory */
	protected $conferenceCategoryModel;

	/** @var  \App\Model\AcmCategory */
	protected $acmCategoryModel;

	/** @var  \App\Model\Publication */
	protected $publicationModel;

	/** @var  \App\Model\ConferenceYearIsIndexed */
	protected $conferenceYearIsIndexedModel;

	/** @var  \App\Factories\IConferenceCategoryListFactory */
	protected $conferenceCategoryListFactory;

	/** @var  \App\Factories\IAcmCategoryListFactory */
	protected $acmCategoryListFactory;

	/** @var  \App\Factories\IConferenceCrudFactory */
	protected $conferenceCrudFactory;

	protected $currentConferenceId = 0;

	protected $currentConferenceYear;

	protected $userFavouriteConferences = null;

	public function __construct() {
		$this->conferenceCategoryFilter = '';
		$this->acmCategoryFilter = '';
	}


	public function createComponentAlphabetFilter($name) {
		$c = new \App\Components\AlphabetFilter\AlphabetFilterComponent($this, $name);
		$c->setAjaxRequest(false);/*->onFilter[] = function($filter) use ($name) {
			if ($this->isAjax()) $this->redrawControl('conferenceYearsShowAll');
		};*/
		$c->onFilter[] = function(){
			$this->resetPagination();
		};
		return $c;
	}

	public function createComponentConferenceYearStateFilter ( ) {
		$c = new \App\Components\ButtonToggle\ButtonGroupComponent([
			'alive'     =>  array(
				'caption'   =>  'Alive',
				'icon'      =>  'tree-deciduous'
			),
			array(
				'caption'   =>  'Archived',
				'icon'      =>  'eye-close',
				'items'     =>  array(
					'archived-last' =>  array(
						'caption'   =>  'Archived - last conference years only'
					),
					'archived'      =>  array(
						'caption'   =>  'Archived',
						//'icon'      =>  'eye-close'
					)
				)
			),
			'all'      =>  array(
				'caption'   =>  'All',
			)
		], 'alive');

		$c->onActiveButtonChanged[] = function(){
			$this->resetPagination();
		};

		return $c;
	}

	public function createComponentConferenceIndividualFilter ( ) {
		$c = new \App\Components\ButtonToggle\ButtonGroupComponent([
			'all'     =>  array(
				'caption'   =>  'All'
			),
			'starred'      =>  array(
				'caption'   =>  'Starred',
				'icon'      =>  'star'
			),
			'suggested'      =>  array(
				'caption'   =>  'Suggested',
				'icon'      =>  'bell'
			)
		], 'all');

		$c->onActiveButtonChanged[] = function(){
			$this->resetPagination();
		};

		return $c;
	}

	protected function getConferenceIndividualFilter() {
		return $this['conferenceIndividualFilter']->getActiveButtonName();
	}

	protected function getConferenceYearStateFilter() {
		return $this['conferenceYearStateFilter']->getActiveButtonName();
	}


	public function renderShowAll($keywords = null) {
		if(!$this->template->records) {    // can be loaded only single one in case of edit
			if ($keywords !== null) {
				$this["searchForm"]->setDefaults(array('keywords' => $keywords));
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
			if($alphabetFilter->getFilter()) $this->records->where('(conference_year.abbreviation LIKE ? OR conference.abbreviation LIKE ?)', $alphabetFilter->getFilter() . '%', strtolower($alphabetFilter->getFilter()) . "%");

			if($this->acmCategoryFilter) {
				$acmCategoriesIds = $this->acmCategoryModel->getAllSubtreeIds(explode(" ", $this->acmCategoryFilter));
				$this->records->where('conference:conference_has_acm_category.acm_category_id IN ?', $acmCategoriesIds);
			}

			if($this->conferenceCategoryFilter) {
				$conferenceCategoriesIds = $this->conferenceCategoryModel->getAllSubtreeIds(explode(" ", $this->conferenceCategoryFilter));
				$this->records->where('conference:conference_has_category.conference_category_id IN ?', $conferenceCategoriesIds);
			}

			switch($conferenceIndividualFilter->getActiveButtonName()) {
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

			if($conferenceYearStateFilter->getActiveButtonName() != 'all')
				$this->records->where('conference_year.state = ?',
					str_replace('archived-last', 'archived', $conferenceYearStateFilter->getActiveButtonName()) );

			$this->records->where('conference.state = ?', 'alive' );

			if($conferenceYearStateFilter->getActiveButtonName() == 'archived-last') {
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
		foreach($this['searchExtrasForm']->getValues() as $k => $val) if($val) { $this->template->extraSearchApplied = true; break; }

		$this->template->deadlineNotificationThreshold = (new \Nette\Utils\DateTime())->add(new \DateInterval('P' . $this->userSettings->deadline_notification_advance . 'D'));

		$this->template->now = new \Nette\Utils\DateTime();
	}

	public function actionShow($id) {
		// load conference year
		$conferenceYear = $this->conferenceYearModel->find($id);

		if(!$conferenceYear) {
			$this->errorFlashMessage('Given conference year does not exist!');
			return;
		}

		$this->currentConferenceId = $conferenceYear->conference_id;
		$this->currentConferenceYear = $conferenceYear;
	}

	public function renderShow() {

		if(!$this->currentConferenceYear) return;

		$conferenceYear = $this->currentConferenceYear;

		$sorting = $this["sortingOthers"];
		/** @var $sorting \NasExt\Controls\SortingControl */

		// load other conference years
		$otherConferenceYears = $this->conferenceYearModel->findAllBy(
			array("conference_id"   =>  $conferenceYear->conference_id)
		)->order($sorting->getColumn() . ' ' . $sorting->getSortDirection());


		// load associated publications
		$allConferenceYearsIds = array($conferenceYear->id);
		foreach($otherConferenceYears as $iConferenceYear) {
			$allConferenceYearsIds[] = $iConferenceYear->id;
		}

		// load associated publications
		$associtatedPublicationsByConferenceYear = array();
		$publications = $this->publicationModel->findAll()->where( 'conference_year_id IN ?', $allConferenceYearsIds );

			// load conference years to the apbcy array
			$associatedPublicationsByConferenceYear[$conferenceYear->id] = array_merge($conferenceYear->toArray(),
				array(
					'current'       =>  true,
					'publications'  =>  array()
				)
			);

			foreach($otherConferenceYears as $iConferenceYear) {
				$associatedPublicationsByConferenceYear[$iConferenceYear->id] = array_merge($iConferenceYear->toArray(),
					array(
						'current'       =>  false,
						'publications'  =>  array()
					)
				);
			}

			// associate publications
			foreach($publications as $publication) {
				$associatedPublicationsByConferenceYear[$publication->conference_year_id]['publications'][]
					= $publication;
			}

			// load authors per publications
			$authorsOfPublications = array();
			foreach($publications as $publication) {
				$authorsOfPublications[$publication->id] = array();
				foreach($publication->related('author_has_publication') as $ahp){
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


		// load categories
		$this->template->acmCategories = array();
		foreach($conferenceYear->ref('conference')->related('conference_has_acm_category') as $chac)
			$this->template->acmCategories[] = $chac->ref('acm_category');

		$this->template->conferenceCategories = array();
		foreach($conferenceYear->ref('conference')->related('conference_has_category') as $chc)
			$this->template->conferenceCategories[] = $chc->ref('conference_category');

		// load publisher
		$this->template->publisher = ($pub = $conferenceYear->ref('publisher')) ? $pub : null;

		// load workshop
		$this->template->parentConferenceYear = (!$conferenceYear->parent_id ? null :
			$this->conferenceYearModel->find($conferenceYear->parent_id));

		$this->template->deadlineNotificationThreshold = (new \Nette\Utils\DateTime())->add(new \DateInterval('P' . $this->userSettings->deadline_notification_advance . 'D'));
		$this->template->now = new \Nette\Utils\DateTime();
	}

	protected function loadWorkshops($conferenceYearId) {
		$associatedWorkshops = $this->conferenceYearModel->findAllBy(array('parent_id' => $conferenceYearId))->order('name ASC');
		$this->template->associatedWorkshops = $associatedWorkshops;
	}


	/**
	 * @return \NasExt\Controls\SortingControl
	 */
	protected function createComponentSorting()
	{
		$tablePrefix = $this->conferenceYearModel->getTableName() . ".";
		$control = $this->sortingControlFactory->create( array(
			'name' => $tablePrefix . 'name',
			'abbreviation' => $tablePrefix . 'abbreviation',
			'w_year' => $tablePrefix . 'w_year',
			'w_from_to' => $tablePrefix . 'w_from',
			//'w_to' => $tablePrefix . 'w_to',
			'deadline' => $tablePrefix . 'deadline',
			'notification' => $tablePrefix . 'notification',
			'finalversion' => $tablePrefix . 'finalversion',
			'location' => $tablePrefix . 'location'
		),  'abbreviation', \NasExt\Controls\SortingControl::ASC);

		return $control;
	}

	/**
	 * @return \NasExt\Controls\SortingControl
	 */
	protected function createComponentSortingOthers()
	{
		$tablePrefix = $this->conferenceYearModel->getTableName() . ".";
		$control = $this->sortingControlFactory->create( array(
			'name' => $tablePrefix . 'name',
			'abbreviation' => $tablePrefix . 'abbreviation',
			'w_year' => $tablePrefix . 'w_year',
			'w_from' => $tablePrefix . 'w_from',
			'w_to' => $tablePrefix . 'w_to',
			'deadline' => $tablePrefix . 'deadline',
			'location' => $tablePrefix . 'location'
		),  'w_year', \NasExt\Controls\SortingControl::DESC);
		$control->setAjaxRequest(true);
		$control->onShort[] = function(){
			$this->redrawControl('otherConferenceYears');
		};
		return $control;
	}

	protected function createComponentConferenceCrud(){
		$c = $this->conferenceCrudFactory->create();

		if(!$this->getUser()->isAllowed('ConferenceAdmin', 'showall')) {
			$c->setReadOnly(true);
		}

		$c->onAdd[] = function($row, $cy) {
			$this->template->newConferenceId = $row->id;
			$this['particularCYCrud'][$row->id]->handleEdit($cy->id);
			$this->redrawControl('showAddCY');
			$this->redrawControl('conferenceYearsShowAll');
		};
		$c->onDelete[] = function() { $this->successFlashMessage('The whole conference has been deleted successfully.'); $this->redirect('Conference:showall'); };
		$c->onEdit[] = function() { $this->successFlashMessage('The whole conference has been edited successfully.'); $this->redrawControl('conferenceYearDetail'); };
		$c->onMergeConferences[] = function(){
			$this->successFlashMessage('The whole conference has been merged with another one successfully');
			$this->redirect('this');
		};

		return $c;
	}

	private function createComponentCYCrud($conferenceId = null) {
		$c = $this->conferenceYearCrudFactory->create(is_null($conferenceId) ? $this->currentConferenceId : $conferenceId);

		// logged user has access to conference administration
		if($this->getUser()->isAllowed('ConferenceAdmin', 'showall')) {
			/*if(!$this->currentConferenceId)
				$c->disallowAction('add');*/
		} else {
			$c->setReadOnly(true);
			if($this->isPU()) {
				$c->allowAction('showRelatedPublications');
			} else {
				$c->disallowAction('showRelatedPublications');
			}
		}

		return $c;
	}

	protected function createComponentParticularCYCrud() {
		return new Multiplier(function ($conferenceId) {
			$c = $this->createComponentCYCrud($conferenceId);

			$c->onAdd[] = function($row) {
				$this->successFlashMessage('The new year of conference was added successfully');
				$this->redirect('Conference:show', $row->id);
			};

			return $c;
		});
	}

	protected function createComponentCrud() {
		$c = $this->createComponentCYCrud();

		$c->onDelete[] = function($row) {
			$this->redirect('Conference:showall');
			$this->successFlashMessage('The year of conference was deleted successfully.');
		};
		$c->onEdit[] = function($row) {
			$this->actionShow($row->id);
			$this->redrawControl('conferenceYearDetail');
			$this->successFlashMessage('The year of conference was edited successfully.');
		};
		$c->onWorkshopsUpdate[] = function($id, $conferenceYearId){
			$this->loadWorkshops($conferenceYearId);
			$this->redrawControl('workshopsShowAllRecords');
		};

		return $c;
	}

	protected function createComponentCrudOthers() {
		$c = $this->createComponentCYCrud();

		$c->onDelete[] = function($row) {
			$this->redrawControl('conferenceYearsShowAll');
			$this->redrawControl('otherConferenceYears');
			$this->successFlashMessage('The year of conference was deleted successfully.');
		};
		$c->onEdit[] = function($row) {
			$this->redrawControl('conferenceYearsShowAll');
			$this->redrawControl('otherConferenceYears');
		};

		$c->onAdd[] = function($row) {
			$this->redrawControl('otherConferenceYears');
			$this->successFlashMessage('The new year of conference was added successfully.');
			$this->redirect('Conference:show', $row->id);
		};

		return $c;
	}

	protected function createComponentCrudList() {
		$c = $this->createComponentCYCrud();

		$c->onDelete[] = function($row) {
			$this->successFlashMessage('The year of conference was deleted successfully.');
			$this->redrawControl('conferenceYearsShowAll');
		};
		$c->onEdit[] = function($row) {
			$this->redrawControl('conferenceYearsShowAll');
			$this->successFlashMessage('The year of conference was edited successfully.');
		};

		return $c;
	}

	protected function createComponentFavouriteToggle(){
		return new Multiplier(function($conferenceId){
			$c = $this->favouriteConferenceToggleFactory->create(
				$this->getUser()->id,
				$conferenceId
			);

			$c->setIsFavourite(
				in_array($conferenceId, $this->getUserFavouriteConferences())
			);

			$c->setAjaxRequest(true);

			$c->onMarkedAsFavourite[] = function(){
				$this->redrawControl('conferenceYearsShowAll');
			};

			return $c;
		});
	}

	protected function getUserFavouriteConferences(){
		// if user favourite conferences are not cached, cache it now
		if($this->userFavouriteConferences === null) {
			$this->userFavouriteConferences =
				$this->submitterFavouriteConferenceModel->getUserFavouriteConferencesIds($this->getUser()->id);
		}
		return $this->userFavouriteConferences;
	}

	protected function createComponentConferenceCategoryList(){
		$c = $this->conferenceCategoryListFactory->create();
		$this->setupCategoryList($c);
		return $c;
	}

	protected function createComponentAcmCategoryList(){
		$c = $this->acmCategoryListFactory->create();
		$this->setupCategoryList($c);
		return $c;
	}

	protected function setupCategoryList(\App\Components\CategoryList\CategoryListComponent &$c) {
		$c->setHasControls(false);
		$c->setHasThreeStates(true);
		$c->setHasDnD(false);
		$c->setHeight(200);
		$c->setWidth(270);
		$c->setIsSelectable(true);
	}

	protected function createComponentSearchExtrasForm() {
		$form = new \App\Forms\BaseForm();

		$conferenceCategoriesInput = $form->addText('conference_categories', 'Conference categories')
			->addRule(\PublicationFormRules::CATEGORIES, 'Valid categories list is required')
			->setValue($this->conferenceCategoryFilter ? $this->conferenceCategoryFilter : '');

		$acmCategoriesInput = $form->addText('acm_categories', 'ACM categories')
			->addRule(\PublicationFormRules::CATEGORIES, 'Valid categories list is required')
			->setValue($this->acmCategoryFilter ? $this->acmCategoryFilter : '');

		// todo later
		// $operator = $form->addRadioList('operator', 'Search operator', array('OR' => 'OR', 'AND' => 'AND'))->setDefaultValue('AND');

		$form->addSubmit('submit', 'Apply');

		$form->onError[] = function() { $this->redrawControl('searchExtrasForm'); };
		$form->onSuccess[] = function (\App\Forms\BaseForm $form) {
			$values = $form->getValuesTransformed();
			$this->conferenceCategoryFilter = $values->conference_categories;
			$this->acmCategoryFilter = $values->acm_categories;
			$this->resetPagination();
		};

		return $form;
	}


	/**
	 * @param \App\Factories\IConferenceCrudFactory $conferenceCrudFactory
	 */
	public function injectConferenceCrudFactory(\App\Factories\IConferenceCrudFactory $conferenceCrudFactory) {
		$this->conferenceCrudFactory = $conferenceCrudFactory;
	}

	/**
	 * @param \App\Model\Conference $conferenceModel
	 */
	public function injectConferenceModel(\App\Model\Conference $conferenceModel) {
		$this->conferenceModel = $conferenceModel;
	}

	/**
	 * @param \App\Model\ConferenceYear $conferenceYearModel
	 */
	public function injectConferenceYearModel(\App\Model\ConferenceYear $conferenceYearModel) {
		$this->conferenceYearModel = $conferenceYearModel;
	}

	/**
	 * @param mixed $conferenceYearCrudFactory
	 */
	public function injectConferenceYearCrudFactory(\App\Factories\IConferenceYearCrudFactory $conferenceYearCrudFactory) {
		$this->conferenceYearCrudFactory = $conferenceYearCrudFactory;
	}

	/**
	 * @param \App\Factories\IFavouriteConferenceToggleFactory $favouriteConferenceToggleFactory
	 */
	public function injectFavouriteConferenceToggleFactory(\App\Factories\IFavouriteConferenceToggleFactory $favouriteConferenceToggleFactory) {
		$this->favouriteConferenceToggleFactory = $favouriteConferenceToggleFactory;
	}

	/**
	 * @param \App\Model\SubmitterFavouriteConference $submitterFavouriteConferenceModel
	 */
	public function injectSubmitterFavouriteConferenceModel(\App\Model\SubmitterFavouriteConference $submitterFavouriteConferenceModel) {
		$this->submitterFavouriteConferenceModel = $submitterFavouriteConferenceModel;
	}

	/**
	 * @param \App\Model\Submitter $submitterModel
	 */
	public function injectSubmitterModel(\App\Model\Submitter $submitterModel) {
		$this->submitterModel = $submitterModel;
	}

	/**
	 * @param \App\Model\ConferenceCategory $conferenceCategoryModel
	 */
	public function injectConferenceCategoryModel(\App\Model\ConferenceCategory $conferenceCategoryModel) {
		$this->conferenceCategoryModel = $conferenceCategoryModel;
	}

	/**
	 * @param \App\Factories\IAcmCategoryListFactory $acmCategoryListFactory
	 */
	public function injectAcmCategoryListFactory(\App\Factories\IAcmCategoryListFactory $acmCategoryListFactory) {
		$this->acmCategoryListFactory = $acmCategoryListFactory;
	}

	/**
	 * @param \App\Factories\IConferenceCategoryListFactory $conferenceCategoryListFactory
	 */
	public function injectConferenceCategoryListFactory(\App\Factories\IConferenceCategoryListFactory $conferenceCategoryListFactory) {
		$this->conferenceCategoryListFactory = $conferenceCategoryListFactory;
	}

	/**
	 * @param \App\Model\AcmCategory $acmCategoryModel
	 */
	public function injectAcmCategoryModel(\App\Model\AcmCategory $acmCategoryModel) {
		$this->acmCategoryModel = $acmCategoryModel;
	}

	/**
	 * @param \App\Model\Publication $publicationModel
	 */
	public function injectPublicationModel(\App\Model\Publication $publicationModel) {
		$this->publicationModel = $publicationModel;
	}

	/**
	 * @param \App\Model\ConferenceYearIsIndexed $conferenceYearIsIndexedModel
	 */
	public function injectConferenceYearIsIndexedModel(\App\Model\ConferenceYearIsIndexed $conferenceYearIsIndexedModel) {
		$this->conferenceYearIsIndexedModel = $conferenceYearIsIndexedModel;
	}


	//use \App\Helpers\SessionPersistence;
}
