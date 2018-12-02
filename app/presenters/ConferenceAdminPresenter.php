<?php

namespace App\Presenters;

use App\Components\AlphabetFilter\AlphabetFilterComponent;
use App\Components\ButtonToggle\ButtonGroupComponent;
use App\CrudComponents\Conference\ConferenceCrud;
use App\Model;
use NasExt\Controls\SortingControl;


/**
 * @persistent(vp, alphabetFilter, showArchivedControl, sorting)
 */
class ConferenceAdminPresenter extends SecuredPresenter {

    /** @var \App\Factories\IAcmCategoryListFactory @inject */
    public $acmCategoryListFactory;

    /** @var \App\Factories\IConferenceCrudFactory @inject */
    public $conferenceCrudFactory;

    /** @var Model\Conference @inject */
    public $conferenceModel;


    /**
     * @param string $name
     * @return AlphabetFilterComponent
     * @throws \ReflectionException
     */
    public function createComponentAlphabetFilter(string $name): AlphabetFilterComponent
    {
        $c = new AlphabetFilterComponent($this, $name);

        /*$c->setAjaxRequest(true)->onFilter[] = function($filter) use ($name) {
            if ($this->isAjax()) $this->redrawControl('conferenceShowAll');
        };*/

        return $c;
    }

    /**
     * @return \App\CrudComponents\Conference\ConferenceCrud
     */
    public function createComponentCrud(): ConferenceCrud
    {
        $c = $this->conferenceCrudFactory->create();

        $c->onAdd[] = function ($row) {
            $this->successFlashMessage(sprintf("Conference %s has been added successfully", $row->name));
            $this->redrawControl('conferenceShowAll');
        };

        $c->onDelete[] = function ($row) {
            $this->successFlashMessage(sprintf("Conference %s has been deleted successfully", $row->name));
            $this->redrawControl('conferenceShowAll');
        };

        $c->onMergeConferences[] = function ($old, $new) {
            $this->successFlashMessage(sprintf("Conference %s has been merged with another conference %s successfully", $old->name, $new->name));
            $this->redrawControl('conferenceShowAll');
        };

        $c->onEdit[] = function ($row) {
            $this->successFlashMessage(sprintf("Conference %s has been edited successfully", $row->name));
            $this->template->records = [$this->conferenceModel->find($row->id)];
            $this->redrawControl('conferenceShowAllRecords');
        };

        if (!$this->isCU()) {
            $c->disallowAction('archivedStateChange');
        }

        $c->onCreateConferenceYearCrud[] = function (\App\CrudComponents\ConferenceYear\ConferenceYearCrud $cy) {
            if (!$this->isCU()) {
                $cy->disallowAction('archivedStateChange');
                $cy->disallowAction('manageWorkshops');
            }
        };

        /*
         * this had sense when conference had 2 states
        $c->onConferenceArchived[] = function($conferenceId, $archived) {
            $this->redrawControl('conferenceShowAll');
        };*/

        return $c;
    }

    /**
     * @param null $keywords
     */
    public function renderShowAll($keywords = null): void
    {
        if (!$this->template->records) {    // can be loaded only single one in case of edit
            if ($keywords !== null) {
                $this["searchForm"]->setDefaults(['keywords' => $keywords]);
                $this->records = $this->conferenceModel->findAllByKw($keywords);
            } else {
                $this->records = $this->conferenceModel->findAll();
            }

            $sorting = $this["sorting"];
            /** @var $sorting \NasExt\Controls\SortingControl */
            $alphabetFilter = $this["alphabetFilter"];
            /** @var $alphabetFilter \App\Components\AlphabetFilter\AlphabetFilterComponent */
            $showArchivedControl = $this["showArchivedControl"];
            /** @var $showArchivedControl \App\Components\ButtonToggle\ButtonGroupComponent */


            if ($alphabetFilter->getFilter()) {
                $this->records->where('(name LIKE ? OR name LIKE ?)', $alphabetFilter->getFilter() . '%', strtolower($alphabetFilter->getFilter()) . "%");
            }

            if ($showArchivedControl->getActiveButtonName() != 'all') {
                $this->records->where('state = ?', $showArchivedControl->getActiveButtonName());
            }

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
            'abbreviation' => 'abbreviation',
            'description' => 'description',
            'first_year' => 'first_year',
        ],  'name', SortingControl::ASC);

        return $control;
    }

    /**
     * @return ButtonGroupComponent
     */
    public function createComponentShowArchivedControl(): ButtonGroupComponent
    {
        return new ButtonGroupComponent([
            'alive'     =>  [
                'caption'   =>  'Alive',
                'icon'      =>  'tree-deciduous'
            ],
            'dead'          =>  [
                'caption'   =>  'Dead',
                'icon'      =>  'thumbs-down'
            ],
            'all'      =>  [
                'caption'   =>  'All',
            ]
        ], 'alive');
    }
}
