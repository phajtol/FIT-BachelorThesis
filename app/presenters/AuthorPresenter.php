<?php

namespace App\Presenters;

use App\Components\AlphabetFilter\AlphabetFilterComponent;
use App\Components\Publication\PublicationControl;
use App\CrudComponents\Author\AuthorCrud;
use App\Model;
use NasExt\Controls\SortingControl;

class AuthorPresenter extends SecuredPresenter {

    /** @var Model\Author @inject */
    public $authorModel;

    /** @var Model\AuthorHasPublication @inject */
    public $authorHasPublicationModel;

    /** @var Model\Publication @inject */
    public $publicationModel;


    /**
     * @param string $name
     * @return AlphabetFilterComponent
     * @throws \ReflectionException
     */
    public function createComponentAlphabetFilter(string $name): AlphabetFilterComponent
    {
        $c = new AlphabetFilterComponent($this, $name);

        $c->setAjaxRequest(true)->onFilter[] = function ($filter) use ($name) {
            if ($this->isAjax()) {
                $this->redrawControl('authorShowAll');
            }
        };

        return $c;
    }


    /**
     * @return AuthorCrud
     */
    public function createComponentCrud(): AuthorCrud{
        $c = new AuthorCrud(
            $this->user,$this->submitterModel, $this->authorModel, $this->publicationModel, $this->authorHasPublicationModel,
            $this, 'crud'
        );

        $c->onAdd[] = function ($row) {
            $this->successFlashMessage(sprintf("Author %s has been added successfully", $row->name . " " . $row->surname));
            $this->redrawControl('authorShowAll');
        };
        $c->onDelete[] = function ($row) {
            $this->successFlashMessage(sprintf("Author %s has been deleted successfully", $row->name . " " . $row->surname));
            $this->redrawControl('authorShowAll');
        };
        $c->onEdit[] = function ($row) {
            $this->successFlashMessage(sprintf("Author %s has been edited successfully", $row->name . " " . $row->surname));
            $this->template->records = [$this->authorModel->find($row->id)];
            $this->redrawControl('authorShowAllRecords');
        };

        return $c;
    }


    /**
     * @param null $keywords
     */
    public function renderShowAll($keywords = null): void {
        if (!$this->template->records) {    // can be loaded only single one in case of edit
            if ($keywords !== null) {
                $this["searchForm"]->setDefaults(['keywords' => $keywords]);
                $this->records = $this->authorModel->findAllByKw($keywords);
            } else {
                $this->records = $this->authorModel->findAll();
            }

            $sorting = $this["sorting"];
            /** @var $sorting \NasExt\Controls\SortingControl */
            $alphabetFilter = $this["alphabetFilter"];
            /** @var $alphabetFilter \App\Components\AlphabetFilter\AlphabetFilterComponent */

            if ($alphabetFilter->getFilter()) {
                $this->records->where('(surname LIKE ? OR surname LIKE ?)', $alphabetFilter->getFilter() . '%', strtolower($alphabetFilter->getFilter()) . "%");
            }

            $this->records->order($sorting->getColumn() . ' ' . $sorting->getSortDirection());
            $this->setupRecordsPaginator();
            $this->template->records = $this->records;
        }
    }

    public function renderDetail(int $id): void
    {
        $this->template->authorDetails = $this->authorModel->getAuthorWithHisTagsAndPublications($id, $this->user->isInRole('admin'));
    }


    /**
     * @return \NasExt\Controls\SortingControl
     */
    protected function createComponentSorting(): SortingControl
    {
        $control = $this->sortingControlFactory->create([
            'name' => 'name',
            'middlename' => 'middlename',
            'surname' => 'surname',
            'user' => 'user.surname'
        ],  'surname', SortingControl::ASC);

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
