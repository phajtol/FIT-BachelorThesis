<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 31.3.2015
 * Time: 16:25
 */

namespace App\CrudComponents\AcmCategory;


use App\CrudComponents\BaseCrudControlsComponent;

class AcmCategoryCrud extends \App\CrudComponents\Category\CategoryCrud {

	/**
	 * @var \App\Model\AcmCategory
	 */
	protected $acmCategoryModel;

	/**
	 * @var \App\Model\ConferenceHasAcmCategory
	 */
	protected $conferenceHasAcmCategoryModel;

	/**
	 * @var \Nette\Security\User
	 */
	protected $loggedUser;

	/**
	 * @var \App\Factories\IConferenceCrudFactory
	 */
	//protected $conferenceCrudFactory;

	public function __construct(
		\Nette\Security\User $loggedUser,
		\App\Model\AcmCategory $acmCategoryModel,
		\App\Model\ConferenceHasAcmCategory $conferenceHasAcmCategoryModel,
		//\App\Factories\IConferenceCrudFactory $conferenceCrudFactory,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {

		parent::__construct("ACM category", $parent, $name);

		$this->conferenceHasAcmCategoryModel = $conferenceHasAcmCategoryModel;
		$this->acmCategoryModel = $acmCategoryModel;
		//$this->conferenceCrudFactory = $conferenceCrudFactory;

		$this->onControlsCreate[] = function(BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedConferences');
		};
	}

	protected function attached($presenter) {
		parent::attached($presenter);
		$this->template->conferencesRelatedToCategory = array();
	}


	public function createComponentCategoryAddForm($name) {
		$form = new AcmCategoryAddForm($this->acmCategoryModel, $this, $name);
		$form->onError[] = function(){
			$this->redrawControl('categoryAddForm');
		};
		$form->onSuccess[] = function(AcmCategoryForm $form) {
                    $formValues = $form->getValuesTransformed();

                    $formValues['parent_id'] = NULL;

                    $record = $this->acmCategoryModel->insert($formValues);

                    if($record) {
                            $this->template->categoryAdded = true;

                            if ($this->presenter->isAjax()) {
                                    $form->clearValues();
                                    $this->redrawControl('categoryAddForm');
                            } else $this->redirect('this');

                            $this->onAdd($record);
                    }
                };
	}

	public function createComponentCategoryEditForm($name) {
		$form = new AcmCategoryEditForm($this, $name);
		$form->onError[] = function(){
			$this->redrawControl('categoryEditForm');
		};
		$form->onSuccess[] = function(AcmCategoryEditForm $form) {
                    $formValues = $form->getValuesTransformed();


                    $this->acmCategoryModel->update($formValues);
                    $record = $this->acmCategoryModel->find($formValues['id']);

                    $this->template->categoryEdited = true;

                    if($this->presenter->isAjax()) {
                            $this->redrawControl('categoryEditForm');
                    } else {
                        $this->redirect('this');
                    }

                    $this->onEdit($record);
            };
	}

	public function createComponentCategoryAddSubForm($name) {
		$form = new AcmCategoryAddSubForm($this->acmCategoryModel, $this, $name);
		$form->onError[] = function(){
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
	}


	public function handleDelete($id) {
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

	public function handleEdit($id) {
		$acmCategory = $this->acmCategoryModel->find($id);

		$this["categoryEditForm"]->setDefaults($acmCategory); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('categoryEditForm');
		}
	}

	public function handleAddSub($id) {
		$acmCategory = $this->acmCategoryModel->find($id);

		$this["categoryAddSubForm"]["parent_id"]->setValue($id); // set up parent id

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('categoryAddSubForm');
		}
	}

	public function handleShowRelatedConferences($id){

		$result = array();
		$categoriesBranchIds = $this->acmCategoryModel->getCategoriesTreeIds($id);

		foreach ($categoriesBranchIds as $row) {
			$categories = $this->conferenceHasAcmCategoryModel->findAllBy(array("acm_category_id" => $row['id']));
			array_push($result, array('categories' => $row, 'conference_has_acm_category' => $categories));
		}

		$this->template->conferencesRelatedToCategory = $result;

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('conferencesRelatedToCategory');
		}
	}

}