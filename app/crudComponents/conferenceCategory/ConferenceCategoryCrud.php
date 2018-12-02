<?php

namespace App\CrudComponents\ConferenceCategory;

use App\CrudComponents\BaseCrudComponent;
use App\CrudComponents\BaseCrudControlsComponent;


class ConferenceCategoryCrud extends \App\CrudComponents\Category\CategoryCrud {


	/** @var \App\Model\ConferenceCategory */
	protected $conferenceCategoryModel;

	/** @var \App\Model\ConferenceHasCategory */
	protected $conferenceHasCategoryModel;

	/** @var \Nette\Security\User */
	protected $loggedUser;

    /**
     * ConferenceCategoryCrud constructor.
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\ConferenceCategory $conferenceCategoryModel
     * @param \App\Model\ConferenceHasCategory $conferenceHasCategoryModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
		\Nette\Security\User $loggedUser,
		\App\Model\ConferenceCategory $conferenceCategoryModel,
		\App\Model\ConferenceHasCategory $conferenceHasCategoryModel,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
		parent::__construct('Conference category', $parent, $name);

		$this->conferenceHasCategoryModel = $conferenceHasCategoryModel;
		$this->conferenceCategoryModel = $conferenceCategoryModel;

		$this->onControlsCreate[] = function (BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedConferences');
		};
	}

    /**
     * @param $presenter
     * @throws \ReflectionException
     */
	protected function attached($presenter): void
    {
		parent::attached($presenter);
		$this->template->conferencesRelatedToCategory = [];
	}

    /**
     * @param string $name
     * @return ConferenceCategoryAddForm
     */
	public function createComponentCategoryAddForm(string $name): ConferenceCategoryAddForm
    {
		$form = new ConferenceCategoryAddForm($this->conferenceCategoryModel, $this, $name);

		$form->onError[] = function () {
			$this->redrawControl('categoryAddForm');
		};

		$form->onSuccess[] = function (ConferenceCategoryAddForm $form) {
		    $formValues = $form->getValuesTransformed();

		    $formValues['parent_id'] = NULL;

		    $record = $this->conferenceCategoryModel->insert($formValues);

		    if ($record) {
			    $this->template->categoryAdded = true;

			    if ($this->presenter->isAjax()) {
				    $form->clearValues();
				    $this->redrawControl('categoryAddForm');
			    } else {
			        $this->redirect('this');
                }

			    $this->onAdd($record);
		    }
		};

		return $form;
	}

    /**
     * @param string $name
     * @return ConferenceCategoryEditForm
     */
	public function createComponentCategoryEditForm(string $name): ConferenceCategoryEditForm
    {
        $form = new ConferenceCategoryEditForm($this, $name);

        $form->onError[] = function () {
            $this->redrawControl('categoryEditForm');
        };

        $form->onSuccess[] = function (ConferenceCategoryEditForm $form) {
		    $formValues = $form->getValuesTransformed();

		    $this->conferenceCategoryModel->update($formValues);
		    $record = $this->conferenceCategoryModel->find($formValues['id']);

		    $this->template->categoryEdited = true;

		    if ($this->presenter->isAjax()) {
			    $this->redrawControl('categoryEditForm');
		    } else {
		        $this->redirect('this');
            }

		    $this->onEdit($record);
        };

        return $form;
	}

    /**
     * @param string $name
     * @return ConferenceCategoryAddSubForm
     */
	public function createComponentCategoryAddSubForm(string $name): ConferenceCategoryAddSubForm
    {
        $form = new ConferenceCategoryAddSubForm($this->conferenceCategoryModel, $this, $name);

        $form->onError[] = function () {
            $this->redrawControl('categoryAddSubForm');
        };

        $form->onSuccess[] = function (ConferenceCategoryAddSubForm $form) {
		    $formValues = $form->getValuesTransformed();

		    $record = $this->conferenceCategoryModel->insert($formValues);

		    if ($record) {
			    $this->template->subcategoryAdded = true;

			    if ($this->presenter->isAjax()) {
				    $this->redrawControl('categoryAddSubForm');
			    } else {
			        $this->redirect('this');
                }

			$this->onAddSub($record);
		    }
        };

        return $form;
	}

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete(int $id): void
    {
		$record = $this->conferenceCategoryModel->find($id);

		if($record) {
			$record->toArray(); // load the object to be passed to the callback
			$this->conferenceCategoryModel->deleteCategoryTreeBranch($id);
			$this->template->categoryDeleted = true;

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deleteCategory');
			}

			$this->onDelete($record);
		}
	}

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleEdit(int $id): void {
		$conferenceCategory = $this->conferenceCategoryModel->find($id);

		$this['categoryEditForm']->setDefaults($conferenceCategory); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('categoryEditForm');
		}
	}

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleAddSub(int $id): void
    {
		$conferenceCategory = $this->conferenceCategoryModel->find($id);

		$this['categoryAddSubForm']['parent_id']->setValue($id); // set up parent id

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('categoryAddSubForm');
		}
	}

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleShowRelatedConferences(int $id): void
    {
		$result = [];
		$categoriesBranchIds = $this->conferenceCategoryModel->getCategoriesTreeIds($id);

		foreach ($categoriesBranchIds as $row) {
			$categories = $this->conferenceHasCategoryModel->findAllBy(["conference_category_id" => $row['id']]);
			array_push($result, ['categories' => $row, 'conference_has_category' => $categories]);
		}

		$this->template->conferencesRelatedToCategory = $result;

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('conferencesRelatedToCategory');
		}
	}

}