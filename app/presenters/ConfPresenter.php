<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 15.3.2015
 * Time: 21:07
 */

namespace App\Presenters;


class ConfPresenter extends SecuredPresenter {

	public function createComponentCrud(){
		$c = new \App\CrudComponents\ConferenceYear\ConferenceYearCrud (
			3,
			$this->user, $this->context->Publisher,
			$this->context->Publication, $this->context->ConferenceYear, $this->context->Conference,
			$this, 'crud'
		);

		$c->onAdd[] = function($row){
			$this->successFlashMessage(sprintf("Conference year %s has been added successfully", $row->name));
			$this->redrawControl('conferenceYearShowAll');
		};
		$c->onDelete = function($row) {
			$this->successFlashMessage(sprintf("Conference year %s has been deleted successfully", $row->name));
			$this->redrawControl('conferenceYearShowAll');
		};
		$c->onEdit = function($row) {
			$this->successFlashMessage(sprintf("Conference year %s has been edited successfully", $row->name));
			$this->redrawControl('conferenceYearShowAll');
		};

		return $c;
	}


	public function renderShowAll($keywords = null) {
		if ($keywords !== null) {
			$this->records = $this->context->Publisher->findAllByKw($keywords);
		} else {
			$this->records = $this->context->Publisher->findAll();
		}

		$sorting = $this["sorting"];
		/** @var $sorting \NasExt\Controls\SortingControl */

		$this->records->order($sorting->getColumn() . ' ' . $sorting->getSortDirection());

		$this->setupRecordsPaginator();

		$this->template->records = $this->records;
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