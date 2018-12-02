<?php

namespace App\Presenters;


use App\CrudComponents\DocumentIndex\DocumentIndexCrud;

class DocumentIndexPresenter extends SecuredPresenter {

	/** @var  \App\Factories\IDocumentIndexCrudFactory @inject */
	public $documentIndexCrudFactory;

	/** @var  \App\Model\DocumentIndex @inject */
	public $documentIndexModel;


    /**
     * @return \App\CrudComponents\DocumentIndex\DocumentIndexCrud
     */
	public function createComponentCrud(): DocumentIndexCrud
    {
		$c = $this->documentIndexCrudFactory->create();

		$c->onAdd[] = function ($row) {
			$this->successFlashMessage("Document index has been added successfully");
			$this->redrawControl('showAll');
		};

		$c->onDelete[] = function ($row) {
			$this->successFlashMessage("Document index has been deleted successfully");
			$this->redrawControl('showAll');
		};

		$c->onEdit[] = function ($row) {
			$this->successFlashMessage("Document index has been edited successfully");
			$this->template->records = array($this->documentIndexModel->find($row->id));
			$this->redrawControl('showAllRecords');
		};

		return $c;
	}

    /**
     * @param null $keywords
     */
	public function renderShowAll($keywords = null): void
    {
		if(!$this->template->records) {    // can be loaded only single one in case of edit
			$this->template->records = $this->documentIndexModel->findAll()->order('name ASC');
		}
	}

}
