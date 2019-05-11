<?php

namespace App\Presenters;

use App\Components\AlphabetFilter\AlphabetFilterComponent;
use App\Components\Publication\PublicationControl;
use App\CrudComponents\Journal\JournalCrud;
use App\Model;
use NasExt\Controls\SortingControl;


class JournalPresenter extends SecuredPresenter {

    /** @var Model\Journal @inject */
    public $journalModel;

    /** @var Model\Publication @inject */
    public $publicationModel;

    /** @var Model\Author @inject */
    public $authorModel;

    /** @var Model\JournalIsbn @inject */
    public $journalIsbnModel;


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
			    $this->redrawControl('journalShowAll');
            }
		};

		return $c;
	}

    /**
     * @return \App\CrudComponents\Journal\JournalCrud
     */
	public function createComponentCrud(): JournalCrud
    {
		$c = new JournalCrud(
			$this->user,
			$this->journalModel,
			$this->publicationModel,
			$this->authorModel,
			$this->journalIsbnModel,
			$this, 'crud'
		);

		$c->onAdd[] = function ($row) {
			$this->successFlashMessage(sprintf("Journal %s has been added successfully", $row->name));

			if ($this->isLinkCurrent(':Journal:detail')) {
			    $this->redirect(':Journal:detail', $row->id);
            } else {
                $this->redrawControl('journalShowAll');
            }
		};

		$c->onDelete[] = function ($row) {
			$this->successFlashMessage(sprintf("Journal %s has been deleted successfully", $row->name));

            if ($this->isLinkCurrent(':Journal:detail')) {
                $this->redirect(':Journal:showall');
            } else {
                $this->redrawControl('journalShowAll');
            }
		};

		$c->onEdit[] = function ($row) {
			$this->successFlashMessage(sprintf("Journal %s has been edited successfully", $row->name));
			$this->template->records = array($this->journalModel->find($row->id));
			$this->redrawControl('journalShowAllRecords');
		};

		return $c;
	}

    /**
     * @param null $keywords
     */
	public function renderShowAll($keywords = null): void
    {
		if(!$this->template->records) {    // can be loaded only single one in case of edit
			if ($keywords !== null) {
				$this["searchForm"]->setDefaults(['keywords' => $keywords]);
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
     * @param int $id
     */
	public function renderDetail(int $id): void
    {
        $publicationIds = [];
        $journalDetails = $this->journalModel->getJournalWithIsbnsAndPublications($id);

        foreach ($journalDetails['publications'] as $publication) {
            $publicationIds[] = $publication->id;
        }

        $authorsByPubId = $this->authorModel->getAuthorsByMultiplePubIds($publicationIds);

        $this->template->journalDetails = $journalDetails;
        $this->template->authorsByPubId = $authorsByPubId;
    }


	/**
	 * @return \NasExt\Controls\SortingControl
	 */
	protected function createComponentSorting(): SortingControl
	{
		$control = $this->sortingControlFactory->create([
			'name' => 'name',
			'doi'  => 'doi',
			'abbreviation' => 'abbreviation'
		],  'name', SortingControl::ASC);

		return $control;
	}

    /**
     * @return PublicationControl
     */
	protected function createComponentPublication(): PublicationControl
    {
        return new PublicationControl();
    }
}
