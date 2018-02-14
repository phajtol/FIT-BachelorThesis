<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 15.3.2015
 * Time: 21:07
 */

namespace App\Presenters;

use App\Model;

class PublisherPresenter extends SecuredPresenter {


  /** @var Model\Author @inject */
  public $authorModel;

  /** @var Model\Publisher @inject */
  public $publisherModel;

  /** @var Model\Publication @inject */
  public $publicationModel;

  /** @var Model\ConferenceYear @inject */
  public $conferenceYearModel;


	public function createComponentAlphabetFilter($name) {
		$c = new \App\Components\AlphabetFilter\AlphabetFilterComponent($this, $name);
		$c->setAjaxRequest(true)->onFilter[] = function($filter) use ($name) {
			if ($this->isAjax()) $this->redrawControl('publisherShowAll');
		};
		return $c;
	}


	public function createComponentCrud(){
		$c = new \App\CrudComponents\Publisher\PublisherCrud(
			$this->user, $this->publisherModel,
			$this->publicationModel, $this->conferenceYearModel,
			$this, 'crud'
		);

		$c->onAdd[] = function($row){
			$this->successFlashMessage(sprintf("Publisher %s has been added successfully", $row->name));
			$this->redrawControl('publisherShowAll');
		};
		$c->onDelete = function($row) {
			$this->successFlashMessage(sprintf("Publisher %s has been deleted successfully", $row->name));
			$this->redrawControl('publisherShowAll');
		};
		$c->onEdit = function($row) {
			$this->successFlashMessage(sprintf("Publisher %s has been edited successfully", $row->name));
			$this->redrawControl('publisherShowAllRecords');
		};

		return $c;
	}


	public function renderShowAll($keywords = null) {
		if(!$this->template->records) {
			if ($keywords !== null) {
				$this["searchForm"]->setDefaults(array('keywords' => $keywords));
				$this->records = $this->publisherModel->findAllByKw($keywords);
			} else {
				$this->records = $this->publisherModel->findAll();
			}

			$sorting = $this["sorting"];
			/** @var $sorting \NasExt\Controls\SortingControl */
			$alphabetFilter = $this["alphabetFilter"];
			/** @var $alphabetFilter \App\Components\AlphabetFilter\AlphabetFilterComponent */

			if($alphabetFilter->getFilter()) $this->records->where('(name LIKE ? OR name LIKE ?)', $alphabetFilter->getFilter() . '%', strtolower($alphabetFilter->getFilter()) . "%");

			$this->records->order($sorting->getColumn() . ' ' . $sorting->getSortDirection());

			$this->setupRecordsPaginator();

			$this->template->records = $this->records;
		}
	}


	/**
	 * @return \NasExt\Controls\SortingControl
	 */
	protected function createComponentSorting()
	{
		$control = $this->sortingControlFactory->create( array(
			'name' => 'name',
			'address' => 'address',
		),  'name', \NasExt\Controls\SortingControl::ASC);

		return $control;
	}

}
