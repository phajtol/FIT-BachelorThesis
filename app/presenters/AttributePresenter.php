<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 27.3.2015
 * Time: 23:21
 */

namespace App\Presenters;


class AttributePresenter extends BasePresenter {

	public function createComponentCrud(){
		$c = new \App\CrudComponents\Attribute\AttributeCrud(
			$this->user, $this->context->Attribute, $this->context->AttribStorage,
			$this, 'crud'
		);

		$c->onAdd[] = function($row){
			$this->successFlashMessage(sprintf("Attribute %s has been added successfully", $row->name));
			$this->redrawControl('attributeShowAll');
		};
		$c->onDelete[] = function($row) {
			$this->successFlashMessage(sprintf("Attribute %s has been deleted successfully", $row->name));
			$this->redrawControl('attributeShowAll');
		};
		$c->onEdit[] = function($row) {
			$this->successFlashMessage(sprintf("Attribute %s has been edited successfully", $row->name));
			$this->template->records = array($this->context->Attribute->find($row->id));
			$this->redrawControl('attributeShowAllRecords');
		};

		return $c;
	}


	public function renderShowAll($keywords = null) {
		if(!$this->template->records) {    // can be loaded only single one in case of edit
			if ($keywords !== null) {
				$this["searchForm"]->setDefaults(array('keywords' => $keywords));
				$this->records = $this->context->Attribute->findAllByKw($keywords);
			} else {
				$this->records = $this->context->Attribute->findAll();
			}

			$sorting = $this["sorting"];
			/** @var $sorting \NasExt\Controls\SortingControl */

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
			'description' => 'description',
		),  'name', \NasExt\Controls\SortingControl::ASC);

		return $control;
	}


}