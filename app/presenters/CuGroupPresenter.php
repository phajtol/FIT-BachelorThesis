<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.4.2015
 * Time: 3:04
 */

namespace App\Presenters;


class CuGroupPresenter extends SecuredPresenter {

	/**
	 * @var \App\Factories\ICuGroupCrudFactory @inject
	 */
	public $cuGroupCrudFactory;

	/**
	 * @var \App\Model\CuGroup @inject
	 */
	public $cuGroupModel;

	public function createComponentCrud(){
		$c = $this->cuGroupCrudFactory->create();

		$c->onAdd[] = function($row){
			$this->successFlashMessage(sprintf("Conference user group %s has been added successfully", $row->name));
			$this->redrawControl('cuGroupShowAll');
		};
		$c->onDelete[] = function($row) {
			$this->successFlashMessage(sprintf("Conference user group %s has been deleted successfully", $row->name));
			$this->redrawControl('cuGroupShowAll');
		};
		$c->onEdit[] = function($row) {
			$this->successFlashMessage(sprintf("Conference user group %s has been edited successfully", $row->name));
			$this->redrawControl('cuGroupShowAllRecords');
		};

		return $c;
	}

	public function renderShowAll() {
		if(!$this->template->records) {    // can be loaded only single one in case of edit

			$this->records = $this->cuGroupModel->findAll();

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
			'name' => 'name'
		),  'name', \NasExt\Controls\SortingControl::ASC);

		return $control;
	}


}
