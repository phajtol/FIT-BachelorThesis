<?php

namespace App\Presenters;

use Nette,
    App\Model;

class AuthorPresenter extends SecuredPresenter {

    public function createComponentAlphabetFilter($name) {
        $c = new \App\Components\AlphabetFilter\AlphabetFilterComponent($this, $name);
        $c->setAjaxRequest(true)->onFilter[] = function($filter) use ($name) {
            if ($this->isAjax()) $this->redrawControl('authorShowAll');
        };
        return $c;
    }

    public function createComponentCrud(){
        $c = new \App\CrudComponents\Author\AuthorCrud(
            $this->user,$this->submitterModel, $this->context->Author, $this->context->AuthorHasPublication,
            $this, 'crud'
        );

        $c->onAdd[] = function($row){
            $this->successFlashMessage(sprintf("Author %s has been added successfully", $row->name . " " . $row->surname));
            $this->redrawControl('authorShowAll');
        };
        $c->onDelete[] = function($row) {
            $this->successFlashMessage(sprintf("Author %s has been deleted successfully", $row->name . " " . $row->surname));
            $this->redrawControl('authorShowAll');
        };
        $c->onEdit[] = function($row) {
            $this->successFlashMessage(sprintf("Author %s has been edited successfully", $row->name . " " . $row->surname));
            $this->template->records = array($this->context->Author->find($row->id));
            $this->redrawControl('authorShowAllRecords');
        };

        return $c;
    }


    public function renderShowAll($keywords = null) {
        if(!$this->template->records) {    // can be loaded only single one in case of edit
            if ($keywords !== null) {
                $this["searchForm"]->setDefaults(array('keywords' => $keywords));
                $this->records = $this->context->Author->findAllByKw($keywords);
            } else {
                $this->records = $this->context->Author->findAll();
            }

            $sorting = $this["sorting"];
            /** @var $sorting \NasExt\Controls\SortingControl */
            $alphabetFilter = $this["alphabetFilter"];
            /** @var $alphabetFilter \App\Components\AlphabetFilter\AlphabetFilterComponent */

            if($alphabetFilter->getFilter()) $this->records->where('(surname LIKE ? OR surname LIKE ?)', $alphabetFilter->getFilter() . '%', strtolower($alphabetFilter->getFilter()) . "%");

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
            'middlename' => 'middlename',
            'surname' => 'surname',
            'user' => 'user.surname'
        ),  'surname', \NasExt\Controls\SortingControl::ASC);

        return $control;
    }

}
