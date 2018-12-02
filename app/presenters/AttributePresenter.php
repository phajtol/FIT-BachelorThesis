<?php

namespace App\Presenters;

use App\CrudComponents\Attribute\AttributeCrud;
use App\Model;
use NasExt\Controls\SortingControl;


class AttributePresenter extends BasePresenter {

    /** @var Model\Attribute @inject */
    public $attributeModel;

    /** @var Model\AttribStorage @inject */
    public $attribStorageModel;


    /**
     * @return AttributeCrud
     */
	public function createComponentCrud(): AttributeCrud
    {
		$c = new AttributeCrud(
			$this->user, $this->attributeModel, $this->attribStorageModel,
			$this, 'crud'
		);

		$c->onAdd[] = function ($row) {
			$this->successFlashMessage(sprintf("Attribute %s has been added successfully", $row->name));
			$this->redrawControl('attributeShowAll');
		};

		$c->onDelete[] = function ($row) {
			$this->successFlashMessage(sprintf("Attribute %s has been deleted successfully", $row->name));
			$this->redrawControl('attributeShowAll');
		};

		$c->onEdit[] = function ($row) {
			$this->successFlashMessage(sprintf("Attribute %s has been edited successfully", $row->name));
			$this->template->records = [$this->attributeModel->find($row->id)];
			$this->redrawControl('attributeShowAllRecords');
		};

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
				$this->records = $this->attributeModel->findAllByKw($keywords);
			} else {
				$this->records = $this->attributeModel->findAll();
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
	protected function createComponentSorting(): SortingControl
	{
		$control = $this->sortingControlFactory->create([
			'name' => 'name',
			'description' => 'description',
		],  'name', SortingControl::ASC);

		return $control;
	}
}
