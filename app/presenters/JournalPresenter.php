<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 26.3.2015
 * Time: 13:06
 */

namespace App\Presenters;

use Nette,
	App\Model;

class JournalPresenter extends SecuredPresenter {
  /** @var Model\Journal @inject */
  public $journalModel;

  /** @var Model\Publication @inject */
  public $publicationModel;

  /** @var Model\JournalIsbn @inject */
  public $journalIsbnModel;

	public function createComponentAlphabetFilter($name) {
		$c = new \App\Components\AlphabetFilter\AlphabetFilterComponent($this, $name);
		$c->setAjaxRequest(true)->onFilter[] = function($filter) use ($name) {
			if ($this->isAjax()) $this->redrawControl('journalShowAll');
		};
		return $c;
	}

	public function createComponentCrud(){
		$c = new \App\CrudComponents\Journal\JournalCrud(
			$this->user,
			$this->journalModel,
			$this->publicationModel,
			$this->journalIsbnModel,
			$this, 'crud'
		);

		$c->onAdd[] = function($row){
			$this->successFlashMessage(sprintf("Journal %s has been added successfully", $row->name));
			$this->redrawControl('journalShowAll');
		};
		$c->onDelete[] = function($row) {
			$this->successFlashMessage(sprintf("Journal %s has been deleted successfully", $row->name));
			$this->redrawControl('journalShowAll');
		};
		$c->onEdit[] = function($row) {
			$this->successFlashMessage(sprintf("Journal %s has been edited successfully", $row->name));
			$this->template->records = array($this->journalModel->find($row->id));
			$this->redrawControl('journalShowAllRecords');
		};

		return $c;
	}


	public function renderShowAll($keywords = null) {
		if(!$this->template->records) {    // can be loaded only single one in case of edit
			if ($keywords !== null) {
				$this["searchForm"]->setDefaults(array('keywords' => $keywords));
				$this->records = $this->journalModel->findAllByKw($keywords);
			} else {
				$this->records = $this->journalModel->findAll();
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
			'doi'  => 'doi',
			'abbreviation' => 'abbreviation'
		),  'name', \NasExt\Controls\SortingControl::ASC);

		return $control;
	}

}
