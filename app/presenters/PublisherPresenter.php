<?php

namespace App\Presenters;

use App\Components\AlphabetFilter\AlphabetFilterComponent;
use App\CrudComponents\Publisher\PublisherCrud;
use App\Model;
use NasExt\Controls\SortingControl;


class PublisherPresenter extends SecuredPresenter {

    /** @var Model\Author @inject */
    public $authorModel;

    /** @var Model\Publisher @inject */
    public $publisherModel;

    /** @var Model\Publication @inject */
    public $publicationModel;

    /** @var Model\ConferenceYear @inject */
    public $conferenceYearModel;


    /**
     * @param string $name
     * @return \App\Components\AlphabetFilter\AlphabetFilterComponent
     * @throws \ReflectionException
     */
	public function createComponentAlphabetFilter(string $name): AlphabetFilterComponent
    {
		$c = new AlphabetFilterComponent($this, $name);

		$c->setAjaxRequest(true)->onFilter[] = function ($filter) use ($name) {
			if ($this->isAjax()) {
			    $this->redrawControl('publisherShowAll');
            }
		};

		return $c;
	}

    /**
     * @return PublisherCrud
     */
	public function createComponentCrud(): PublisherCrud
    {
		$c = new PublisherCrud(
			$this->user, $this->publisherModel,
			$this->publicationModel, $this->conferenceYearModel,
			$this, 'crud'
		);

		$c->onAdd[] = function ($row) {
			$this->successFlashMessage(sprintf("Publisher %s has been added successfully", $row->name));
			$this->redrawControl('publisherShowAll');
		};

		$c->onDelete = function ($row) {
			$this->successFlashMessage(sprintf("Publisher %s has been deleted successfully", $row->name));
			$this->redrawControl('publisherShowAll');
		};

		$c->onEdit = function ($row) {
			$this->successFlashMessage(sprintf("Publisher %s has been edited successfully", $row->name));
			$this->redrawControl('publisherShowAllRecords');
		};

		return $c;
	}

    /**
     * @param null $keywords
     */
	public function renderShowAll($keywords = null): void
    {
		if (!$this->template->records) {
			if ($keywords !== null) {
				$this["searchForm"]->setDefaults(['keywords' => $keywords]);
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
	protected function createComponentSorting(): SortingControl
	{
		$control = $this->sortingControlFactory->create([
			'name' => 'name',
			'address' => 'address',
		],  'name', SortingControl::ASC);

		return $control;
	}

}
