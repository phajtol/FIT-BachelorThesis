<?php

namespace App\Presenters;

use App\CrudComponents\ConferenceYear\ConferenceYearCrud;
use App\Model;
use NasExt\Controls\SortingControl;


class ConfPresenter extends SecuredPresenter {

    /** @var Model\Publisher @inject */
    public $publisherModel;

    /** @var Model\Publication @inject */
    public $publicationModel;

    /** @var Model\ConferenceYear @inject */
    public $conferenceYearModel;

    /** @var Model\Conference @inject */
    public $conferenceModel;


    /**
     * @return \App\CrudComponents\ConferenceYear\ConferenceYearCrud
     */
	public function createComponentCrud(): ConferenceYearCrud
    {
		$c = new ConferenceYearCrud (
			3,
			$this->user, $this->publisherModel,
			$this->publicationModel, $this->conferenceYearModel, $this->conferenceModel,
			$this, 'crud'
		);

		$c->onAdd[] = function ($row) {
			$this->successFlashMessage(sprintf("Conference year %s has been added successfully", $row->name));
			$this->redrawControl('conferenceYearShowAll');
		};

		$c->onDelete = function ($row) {
			$this->successFlashMessage(sprintf("Conference year %s has been deleted successfully", $row->name));
			$this->redrawControl('conferenceYearShowAll');
		};

		$c->onEdit = function ($row) {
			$this->successFlashMessage(sprintf("Conference year %s has been edited successfully", $row->name));
			$this->redrawControl('conferenceYearShowAll');
		};

		return $c;
	}

    /**
     * @param null $keywords
     */
	public function renderShowAll($keywords = null): void
    {
		if ($keywords !== null) {
			$this->records = $this->publisherModel->findAllByKw($keywords);
		} else {
			$this->records = $this->publisherModel->findAll();
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
	protected function createComponentSorting(): SortingControl
	{
		$control = $this->sortingControlFactory->create( array(
			'name' => 'name',
			'address' => 'address',
		),  'name', SortingControl::ASC);

		return $control;
	}

}
