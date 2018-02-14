<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 15.4.2015
 * Time: 17:07
 */

namespace App\Presenters;


class DocumentIndexPresenter extends SecuredPresenter {

	/** @var  \App\Factories\IDocumentIndexCrudFactory @inject */
	public $documentIndexCrudFactory;

	/** @var  \App\Model\DocumentIndex @inject */
	public $documentIndexModel;


	public function createComponentCrud(){
		$c = $this->documentIndexCrudFactory->create();

		$c->onAdd[] = function($row){
			$this->successFlashMessage("Document index has been added successfully");
			$this->redrawControl('showAll');
		};
		$c->onDelete[] = function($row) {
			$this->successFlashMessage("Document index has been deleted successfully");
			$this->redrawControl('showAll');
		};
		$c->onEdit[] = function($row) {
			$this->successFlashMessage("Document index has been edited successfully");
			$this->template->records = array($this->documentIndexModel->find($row->id));
			$this->redrawControl('showAllRecords');
		};

		return $c;
	}


	public function renderShowAll($keywords = null) {
		if(!$this->template->records) {    // can be loaded only single one in case of edit
			$this->template->records = $this->documentIndexModel->findAll()->order('name ASC');
		}
	}


}
