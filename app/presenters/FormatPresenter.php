<?php

namespace App\Presenters;

use Nette,
    App\Model,
    \VisualPaginator,
    Nette\Diagnostics\Debugger;

class FormatPresenter extends SecuredPresenter {


    /** @var  \App\Factories\IFormatCrudFactory */
    protected $formatCrudFactory;

    /** @var  \App\Model\Format */
    protected $formatModel;

    /**
     * @param \App\Factories\IFormatCrudFactory $formatCrudFactory
     */
    public function injectFormatCrudFactory(\App\Factories\IFormatCrudFactory $formatCrudFactory) {
        $this->formatCrudFactory = $formatCrudFactory;
    }

    /**
     * @param \App\Model\Format $formatModel
     */
    public function injectFormatModel(\App\Model\Format $formatModel) {
        $this->formatModel = $formatModel;
    }



    public function createComponentCrud(){
        $c = $this->formatCrudFactory->create();

        $c->onAdd[] = function($row){
            $this->successFlashMessage("Format has been added successfully");
            $this->redrawControl('showAll');
        };
        $c->onDelete[] = function($row) {
            $this->successFlashMessage("Format has been deleted successfully");
            $this->redrawControl('showAll');
        };
        $c->onEdit[] = function($row) {
            $this->successFlashMessage("Format has been edited successfully");
            $this->template->records = array($this->formatModel->find($row->id));
            $this->redrawControl('showAllRecords');
        };

        return $c;
    }


    public function renderShowAll($keywords = null) {
        if(!$this->template->records) {    // can be loaded only single one in case of edit
            $this->template->records = $this->formatModel->findAll()->order('name ASC');
        }
    }



}
