<?php

namespace App\CrudComponents\AcmCategory;


use App\CrudComponents\BaseCrudControlsComponent;

class AcmCategoryCrud extends \App\CrudComponents\Category\CategoryCrud {

	/** @var \App\Model\AcmCategory */
	protected $acmCategoryModel;

	/** @var \App\Model\ConferenceHasAcmCategory */
	protected $conferenceHasAcmCategoryModel;

	/** @var \Nette\Security\User */
	protected $loggedUser;


    /**
     * AcmCategoryCrud constructor.
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\AcmCategory $acmCategoryModel
     * @param \App\Model\ConferenceHasAcmCategory $conferenceHasAcmCategoryModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
		\Nette\Security\User $loggedUser,
		\App\Model\AcmCategory $acmCategoryModel,
		\App\Model\ConferenceHasAcmCategory $conferenceHasAcmCategoryModel,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
		parent::__construct('ACM category', $parent, $name);

		$this->conferenceHasAcmCategoryModel = $conferenceHasAcmCategoryModel;
		$this->acmCategoryModel = $acmCategoryModel;

		$this->onControlsCreate[] = function (BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedConferences');
		};
	}

	protected function attached($presenter): void
    {
		parent::attached($presenter);
		$this->template->conferencesRelatedToCategory = [];
	}

    /**
     * @param string $name
     * @return AcmCategoryAddForm
     */
	public function createComponentCategoryAddForm(string $name): AcmCategoryAddForm
    {
		$form = new AcmCategoryAddForm($this->acmCategoryModel, $this, $name);

		$form->onError[] = function () {
			$this->redrawControl('categoryAddForm');
		};

		$form->onSuccess[] = function (AcmCategoryForm $form) {
            $formValues = $form->getValuesTransformed();

            $formValues['parent_id'] = NULL;

            $record = $this->acmCategoryModel->insert($formValues);

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
     * @return AcmCategoryEditForm
     */
	public function createComponentCategoryEditForm(string $name): AcmCategoryEditForm
    {
		$form = new AcmCategoryEditForm($this, $name);

		$form->onError[] = function () {
			$this->redrawControl('categoryEditForm');
		};

		$form->onSuccess[] = function (AcmCategoryEditForm $form) {
            $formValues = $form->getValuesTransformed();

            $this->acmCategoryModel->update($formValues);
            $record = $this->acmCategoryModel->find($formValues['id']);

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
     * @return AcmCategoryAddSubForm
     */
	public function createComponentCategoryAddSubForm(string $name): AcmCategoryAddSubForm
    {
		$form = new AcmCategoryAddSubForm($this->acmCategoryModel, $this, $name);

		$form->onError[] = function () {
			$this->redrawControl('categoryAddSubForm');
		};

		$form->onSuccess[] = function(AcmCategoryAddSubForm $form) {
		    $formValues = $form->getValuesTransformed();

            $record = $this->acmCategoryModel->insert($formValues);

            if($record) {
                $this->template->subcategoryAdded = true;

                if ($this->presenter->isAjax()) {
                    $this->redrawControl('categoryAddSubForm');
                } else $this->redirect('this');

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
		$record = $this->acmCategoryModel->find($id);

		if($record) {
			$record->toArray(); // load the object to be passed to the callback
			$this->acmCategoryModel->deleteCategoryTreeBranch($id);
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
	public function handleEdit(int $id): void
    {
		$acmCategory = $this->acmCategoryModel->find($id);
		$this['categoryEditForm']->setDefaults($acmCategory); // set up new values

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
		$acmCategory = $this->acmCategoryModel->find($id);
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
		$categoriesBranchIds = $this->acmCategoryModel->getCategoriesTreeIds($id);

		foreach ($categoriesBranchIds as $row) {
			$categories = $this->conferenceHasAcmCategoryModel->findAllBy(['acm_category_id' => $row['id']]);
			array_push($result, ['categories' => $row, 'conference_has_acm_category' => $categories]);
		}

		$this->template->conferencesRelatedToCategory = $result;

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('conferencesRelatedToCategory');
		}
	}

}