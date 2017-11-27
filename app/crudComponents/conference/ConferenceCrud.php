<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 26.3.2015
 * Time: 19:50
 */

namespace App\CrudComponents\Conference;


use App\CrudComponents\BaseCrudComponent;
use App\CrudComponents\BaseCrudControlsComponent;
use Nette\Application\UI\Multiplier;

class ConferenceCrud extends BaseCrudComponent {


	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var \App\Model\Conference */
	protected $conferenceModel;

	/** @var \App\Model\ConferenceYear */
	protected $conferenceYearModel;

	/** @var \App\Model\Publication */
	protected $publicationModel;

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


	public $onCreateConferenceYearCrud = [];
	public $onMergeConferences = [];


	/** @persistent */
	public $conferenceId = null;

	/** @var Callable[] event fired when conference is archived / brought back.. ($confId, bool $isArchived) */
	public $onConferenceArchived;


	public function __construct(

		\Nette\Security\User $loggedUser, \App\Model\Conference $conferenceModel, \App\Model\ConferenceYear $conferenceYearModel, \App\Model\Publication $publicationModel, \App\Model\Publisher $publisherModel,
		\App\Model\ConferenceHasAcmCategory $conferenceHasAcmCategoryModel, \App\Model\ConferenceHasCategory $conferenceHasCategoryModel,
		\App\Factories\IAcmCategoryListFactory $acmCategoryListFactory, \App\Factories\IConferenceCategoryListFactory $conferenceCategoryListFactory,
		\App\Factories\IConferenceYearCrudFactory $conferenceYearCrudFactory, \App\Helpers\SortingControlFactory $sortingControlFactory ,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL
	) {
		parent::__construct($parent, $name);

		$this->conferenceModel = $conferenceModel;
		$this->conferenceYearModel = $conferenceYearModel;
		$this->publicationModel = $publicationModel;
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

		$this->onControlsCreate[] = function(BaseCrudControlsComponent &$controlsComponent) use ($conferenceModel) {
			$controlsComponent->addActionAvailable('showConferenceYears');
			$controlsComponent->addActionAvailable('showRelatedPublications');
			$controlsComponent->addActionAvailable('mergeConferences');

			$conference = $conferenceModel->find($controlsComponent->getRecordId());

			if($conference) {
				$conferenceState = $conference->state;

				$conferenceSTM = array(
					'alive'     =>  'dead',
					'dead'      =>  'alive'
				);

				$controlsComponent->addTemplateVars(array(
					'conferenceState' => $conferenceState,
					'nextConferenceState' => $conferenceSTM[$conferenceState],
					'setConferenceStateLink' => $this->link('setConferenceState!', array($controlsComponent->getRecordId(), $conferenceSTM[$conferenceState]))
				));
			}
		};
	}

	protected function createComponentCPToggle() {
		$c = parent::createComponentCPToggle();
		$c->onActiveButtonChanged[] = function(){
			$this->getPresenter()->redrawControl();
		};
		return $c;
	}

	public function createComponentConferenceYear() {

		$parent = $this;

		return new Multiplier(function ($conferenceId) use ($parent) {
			/*
			$c = new \App\CrudComponents\ConferenceYear\ConferenceYearCrud(
				$conferenceId,
				$parent->loggedUser,
				$parent->publisherModel, $parent->publicationModel, $parent->conferenceYearModel, $parent->conferenceModel
			);*/

			$c = $parent->conferenceYearCrudFactory->create($conferenceId);

			$fnRedraw = function($record) use ($parent, $conferenceId) {
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
	
	public function createComponentConferenceAddForm($name){
            $form = new ConferenceAddForm($this->conferenceModel, $this, $name);
            $this->reduceForm($form);
            $form->onError[] = function(){
                    $this->redrawControl('conferenceAddForm');
            };
            $form->onSuccess[] = function(ConferenceAddForm $form) {
		$formValues = $form->getValuesTransformed();

		$formValues['submitter_id'] = $this->loggedUser->id;

		if(isset($form['acm_categories'])) {
			$acm_categories = $formValues['acm_categories'] ? explode(" ", $formValues['acm_categories']) : array();
			unset($formValues["acm_categories"]);
		}

		if(isset($form['conference_categories'])) {
			$conference_categories = $formValues['conference_categories'] ? explode(" ", $formValues['conference_categories']) : array();
			unset($formValues["conference_categories"]);
		}

		$record = $this->conferenceModel->insert($formValues);

		if($record) {
			$this->template->conferenceAdded = true;

			if(isset($acm_categories)) $this->conferenceHasAcmCategoryModel->setAssociatedAcmCategories($record->id, $acm_categories);
			if(isset($conference_categories)) $this->conferenceHasCategoryModel->setAssociatedConferenceCategories($record->id, $conference_categories);

			if ($this->presenter->isAjax()) {
				$form->clearValues();
				$this->redrawControl('conferenceAddForm');
			} else $this->redirect('this');

			$this->onAdd($record);
		}
            };
	}

	public function createComponentConferenceEditForm($name){
            $form = new ConferenceEditForm($this, $name);
            $this->reduceForm($form);
            $form->onError[] = function(){
                    $this->redrawControl('conferenceEditForm');
            };
            $form->onSuccess[] = function(ConferenceEditForm $form) {
		$formValues = $form->getValuesTransformed();

		$formValues['submitter_id'] = $this->loggedUser->id;

		if(isset($form['acm_categories'])) {
			$acm_categories = $formValues['acm_categories'] ? explode(" ", $formValues['acm_categories']) : array();
			unset($formValues["acm_categories"]);
		}

		if(isset($form['conference_categories'])) {
			$conference_categories = $formValues['conference_categories'] ? explode(" ", $formValues['conference_categories']) : array();
			unset($formValues["conference_categories"]);
		}

		$this->conferenceModel->update($formValues);
		$record = $this->conferenceModel->find($formValues['id']);

		$this->template->conferenceEdited = true;

		if(isset($acm_categories)) $this->conferenceHasAcmCategoryModel->setAssociatedAcmCategories($record->id, $acm_categories);
		if(isset($conference_categories)) $this->conferenceHasCategoryModel->setAssociatedConferenceCategories($record->id, $conference_categories);

		if($this->presenter->isAjax()) {
			$this->redrawControl('conferenceEditForm');
		} else $this->redirect('this');

		$this->onEdit($record);
            };
	}


	public function createComponentMergeConferencesForm() {
		$form = new \App\Forms\BaseForm();
		$form->addHidden('source_conference_id')->addRule(\Nette\Forms\Form::INTEGER)->setValue($this->conferenceId);
		$form->addHidden('target_conference_id')->addRule(\Nette\Forms\Form::INTEGER);
		$form->addText('target_conference_name', 'Target conference'); // for typeahead
		$form->addSubmit('send', 'Move this conference');
		$form->setModal(true);
		$form->setAjax(true);

		$form->onSuccess[] = function(\App\Forms\BaseForm $form){
			$source_conference = $this->conferenceModel->find($form['source_conference_id']->getValue());
			$target_conference = $this->conferenceModel->find($form['target_conference_id']->getValue());

			if(!$source_conference) throw new \Exception('Source connference not found');
			if(!$target_conference) throw new \Exception('Target connference not found');

			$source_conference->toArray(); // load the object to be passed to the callback

			$this->conferenceModel->mergeConferences($source_conference->id, $target_conference->id);

			$this->onMergeConferences($source_conference, $target_conference);
			$this->redrawControl('conferencesMerged');
		};

		return $form;
	}

	public function handleDelete($id) {
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

	public function handleEdit($id) {
		$conference = $this->conferenceModel->find($id);

		// load acm categories
		$acm_category_ids = array();
		$acm_category_results = $this->conferenceHasAcmCategoryModel->findAllByConferenceId($id);
		foreach($acm_category_results as $row) $acm_category_ids[] = $row['acm_category_id'];

		if(isset($this['conferenceEditForm']['acm_categories']))
			$this['conferenceEditForm']['acm_categories']->setValue(implode(' ', $acm_category_ids));

		// load conference categories
		$conference_category_ids = array();
		$conference_category_results = $this->conferenceHasCategoryModel->findAllByConferenceId($id);
		foreach($conference_category_results as $row) $conference_category_ids[] = $row['conference_category_id'];

		if(isset($this['conferenceEditForm']['conference_categories']))
			$this['conferenceEditForm']['conference_categories']->setValue(implode(' ', $conference_category_ids));

		$this["conferenceEditForm"]->setDefaults($conference); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('conferenceEditForm');
		}

	}  


	public function handleShowRelatedPublications($id) {
		$ConferenceYearArray = array();
		$counter = 0;
		$counter2 = 0;

		$conferenceRelated = $this->conferenceYearModel->findAllBy(array("conference_id" => $id))->order('w_year DESC');

		foreach ($conferenceRelated as $row) {
			$ConferenceYearArray[$counter] = $row->toArray();

			$publications = $this->publicationModel->findAllBy(array("conference_year_id" => $row['id']));
			$publicationsArray = array();
			foreach ($publications as $pub) {
				$publicationsArray[$counter2]['title'] = $pub['title'];
				$publicationsArray[$counter2]['id'] = $pub['id'];
				$counter2++;
			}
			$counter2 = 0;
			$ConferenceYearArray[$counter]['publication'] = $publicationsArray;
			$counter++;
		}
		$this->template->publicationsRelatedToConference = $ConferenceYearArray;

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('publicationsRelatedToConference');
		}
	}

	public function handleShowConferenceYears($id){
		$this->conferenceId = $id;
		$this->loadConferenceYears();
		if($this->presenter->isAjax()) {
			$this->redrawControl('conferenceYearsBase');
			$this->redrawControl('conferenceYears');
		} else $this->redirect('this');
	}

	public function handleSetConferenceState($id, $state){
		if(!in_array($state, array('alive', 'dead'))) return;
		$this->conferenceModel->update(array(
			'id'        =>  $id,
			'state'     =>  $state
		));
		$this["controls"][$id]->redrawControl();
		$this->onConferenceArchived($id, $state);
	}

	public function handleMergeConferences($id) {
		if($this->isActionAllowed('mergeConferences')) {
			$this->conferenceId = $id;
			$this->redrawControl('mergeConferencesForm');
		}
	}

	public function handleFindConferencesForTypeAhead($query) {
		$records = $this->conferenceModel->findAllByKw($query);
		if($this->conferenceId) $records = $records->where('id != ?', $this->conferenceId);

		$records = $records->order('abbreviation ASC')->limit(20);

		$toSend = array();
		foreach($records as $rec) {
			$toSend[] = $rec->toArray();
		}

		$this->presenter->sendResponse(new \Nette\Application\Responses\JsonResponse($toSend));
	}

	protected function loadConferenceYears(){
		$this->template->conferenceYears = $this->conferenceYearModel->findAllByConferenceId($this->conferenceId)
			->order($this['cYSorting']->getColumn() . ' ' . $this['cYSorting']->getSortDirection());
		$this->template->conferenceId = $this->conferenceId;
	}

	public function render() {

		$this->template->addFormAcmCategoriesElementId = isset($this['conferenceAddForm']['acm_categories'])
			? $this['conferenceAddForm']['acm_categories']->getHtmlId() : null;

		$this->template->editFormAcmCategoriesElementId = isset($this['conferenceEditForm']['acm_categories'])
			? $this['conferenceEditForm']['acm_categories']->getHtmlId() : null;

		$this->template->addFormConferenceCategoriesElementId = isset($this['conferenceAddForm']['conference_categories']) ?
			$this['conferenceAddForm']['conference_categories']->getHtmlId() : null;

		$this->template->editFormConferenceCategoriesElementId = isset($this['conferenceEditForm']['conference_categories']) ?
			$this['conferenceEditForm']['conference_categories']->getHtmlId() : null;

		$this->addDefaultTemplateVars(array(
			"conferenceAdded" => false,
			"conferenceEdited" => false,
			"conferenceDeleted" => false,
			"publicationsRelatedToConference" => array(),
			"conferenceYears" => array(),
			"conferenceId" => $this->conferenceId
		));

		parent::render();
	}

	public function createComponentAcmCategoryListA(){
		return $this->createAcmCategoryList();
	}

	public function createComponentAcmCategoryListE() {
		return $this->createAcmCategoryList();
	}

	protected function createAcmCategoryList () {
		$c = $this->acmCategoryListFactory->create();
		$c->setHeight(180);
		$c->setWidth(375);
		$c->setIsSelectable(true);
		$c->setHasControls(true);
		return $c;
	}


	public function createComponentConferenceCategoryListA(){
		return $this->createConferenceCategoryList();
	}

	public function createComponentConferenceCategoryListE() {
		return $this->createConferenceCategoryList();
	}

	protected function createConferenceCategoryList () {
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
	protected function createComponentCYSorting()
	{
		$tablePrefix = '';
		$control = $this->sortingControlFactory->create( array(
			'name' => $tablePrefix . 'name',
			'abbreviation' => $tablePrefix . 'abbreviation',
			'w_year' => $tablePrefix . 'w_year',
			'w_from' => $tablePrefix . 'w_from',
			'w_to' => $tablePrefix . 'w_to',
			'deadline' => $tablePrefix . 'deadline',
			'notification' => $tablePrefix . 'notification',
			'finalversion' => $tablePrefix . 'finalversion',
			'location' => $tablePrefix . 'location',
			'isbn' => $tablePrefix . 'isbn',
			'issn' => $tablePrefix . 'issn',
			'doi' => $tablePrefix . 'doi',
			'publisher_id' => $tablePrefix . 'publisher_id'
		),  'w_year', \NasExt\Controls\SortingControl::DESC);

		$control->setAjaxRequest(true);
		$control->onShort[] = function(){
			$this->loadConferenceYears();
			$this->redrawControl();
		};

		return $control;
	}


}