<?php

namespace App\CrudComponents\PublicationCategory;

use App\CrudComponents\BaseCrudControlsComponent;
use App\CrudComponents\Category\CategoryCrud;


class PublicationCategoryCrud extends CategoryCrud {

	/** @var \App\Model\Categories */
	protected $publicationCategoryModel;

	/** @var \App\Model\CategoriesHasPublication */
	protected $categoriesHasPublicationModel;

	/** @var \Nette\Security\User */
	protected $loggedUser;


    /**
     * PublicationCategoryCrud constructor.
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\Categories $publicationCategoryModel
     * @param \App\Model\CategoriesHasPublication $categoriesHasPublicationModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
		\Nette\Security\User $loggedUser,
		\App\Model\Categories $publicationCategoryModel,
		\App\Model\CategoriesHasPublication $categoriesHasPublicationModel,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
		parent::__construct('publication category', $parent, $name);

		$this->categoriesHasPublicationModel = $categoriesHasPublicationModel;
		$this->publicationCategoryModel = $publicationCategoryModel;
		$this->loggedUser = $loggedUser;

		$this->template->publicationsRelatedToCategory = [];

		$this->onControlsCreate[] = function (BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedPublications');
		};
	}

    /**
     * @param string $name
     * @return PublicationCategoryAddForm
     */
	public function createComponentCategoryAddForm(string $name): PublicationCategoryAddForm
    {
        $form = new PublicationCategoryAddForm($this->publicationCategoryModel, $this, $name);

        $form->onError[] = function () {
            $this->redrawControl('categoryAddForm');
        };

        $form->onSuccess[] = function (PublicationCategoryAddForm $form){
            $formValues = $form->getValuesTransformed();
		    $formValues['submitter_id'] = $this->loggedUser->id;
		    $formValues['categories_id'] = NULL;

		    $record = $this->publicationCategoryModel->insert($formValues);

		    if($record) {
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
     * @return PublicationCategoryEditForm
     */
	public function createComponentCategoryEditForm(string $name): PublicationCategoryEditForm
    {
        $form = new PublicationCategoryEditForm($this, $name);

        $form->onError[] = function () {
            $this->redrawControl('categoryEditForm');
        };

        $form->onSuccess[] = function (PublicationCategoryEditForm $form) {
		    $formValues = $form->getValuesTransformed();
		    $formValues['submitter_id'] = $this->loggedUser->id;
		    $this->publicationCategoryModel->update($formValues);
		    $record = $this->publicationCategoryModel->find($formValues['id']);
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
     * @return PublicationCategoryAddSubForm
     */
	public function createComponentCategoryAddSubForm(string $name): PublicationCategoryAddSubForm
    {
        $form = new PublicationCategoryAddSubForm($this->publicationCategoryModel, $this, $name);

        $form->onError[] = function () {
            $this->redrawControl('categoryAddSubForm');
        };

        $form->onSuccess[] = function (PublicationCategoryAddSubForm $form) {
		    $formValues = $form->getValuesTransformed();
		    $formValues['submitter_id'] = $this->loggedUser->id;
		    $record = $this->publicationCategoryModel->insert($formValues);

		    if($record) {
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
		$record = $this->publicationCategoryModel->find($id);

		if($record) {
			$record->toArray(); // load the object to be passed to the callback
			$this->publicationCategoryModel->deleteCategoryTreeBranch($id);
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
		$publicationCategory = $this->publicationCategoryModel->find($id);
		$this['categoryEditForm']->setDefaults($publicationCategory); // set up new values

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
		$publicationCategory = $this->publicationCategoryModel->find($id);
		$this['categoryAddSubForm']['categories_id']->setValue($id); // set up parent id

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
	public function handleShowRelatedPublications(int $id): void
    {
		$result = [];
		$categoriesBranchIds = $this->publicationCategoryModel->getCategoriesTreeIds($id);

		foreach ($categoriesBranchIds as $row) {
			$categories = $this->categoriesHasPublicationModel->findAllBy(["categories_id" => $row['id']]);
			array_push($result, ['categories' => $row, 'categories_has_publication' => $categories]);
		}

		$this->template->publicationsRelatedToCategory = $result;

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('publicationsRelatedToCategory');
		}
	}


}