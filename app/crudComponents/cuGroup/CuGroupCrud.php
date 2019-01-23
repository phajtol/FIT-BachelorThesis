<?php

namespace App\CrudComponents\CuGroup;

use App\Components\ConferenceCategoryList\ConferenceCategoryListComponent;
use App\CrudComponents\BaseCrudComponent;
use App\CrudComponents\BaseCrudControlsComponent;


class CuGroupCrud extends BaseCrudComponent {

	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var \App\Model\CuGroup */
	protected $cuGroupModel;

	/** @var \App\Model\SubmitterHasCuGroup */
	protected $submitterHasCuGroupModel;

	/** @var \App\Factories\IConferenceCategoryListFactory */
	protected $conferenceCategoryListFactory;

	/** @var \App\Model\CuGroupHasConferenceCategory */
	protected $cuGroupHasConferenceCategoryModel;


    /**
     * CuGroupCrud constructor.
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\CuGroup $cuGroupModel
     * @param \App\Model\SubmitterHasCuGroup $submitterHasCuGroupModel
     * @param \App\Model\CuGroupHasConferenceCategory $cuGroupHasConferenceCategoryModel
     * @param \App\Factories\IConferenceCategoryListFactory $conferenceCategoryListFactory
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
		\Nette\Security\User $loggedUser,
        \App\Model\CuGroup $cuGroupModel,
        \App\Model\SubmitterHasCuGroup $submitterHasCuGroupModel,
        \App\Model\CuGroupHasConferenceCategory $cuGroupHasConferenceCategoryModel,
		\App\Factories\IConferenceCategoryListFactory $conferenceCategoryListFactory,
		\Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->cuGroupModel = $cuGroupModel;
		$this->submitterHasCuGroupModel = $submitterHasCuGroupModel;
		$this->cuGroupHasConferenceCategoryModel = $cuGroupHasConferenceCategoryModel;
		$this->loggedUser = $loggedUser;

		$this->conferenceCategoryListFactory = $conferenceCategoryListFactory;

		$this->onControlsCreate[] = function (BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedUsers');
		};
	}

    /**
     * @param string $name
     * @return CuGroupAddForm
     */
	public function createComponentCuGroupAddForm(string $name): CuGroupAddForm
    {
        $form = new CuGroupAddForm($this->cuGroupModel, $this, $name);

        $form->onError[] = function () {
            $this->redrawControl('cuGroupAddForm');
        };

        $form->onSuccess[] = function (CuGroupAddForm $form) {
            $formValues = $form->getValuesTransformed();

		    $conferenceCategories = $formValues['conference_categories'] ? explode(" ", $formValues['conference_categories']) : [];
		    unset($formValues['conference_categories']);
		    $record = $this->cuGroupModel->insert($formValues);

		    if($record) {
			    $this->template->cuGroupAdded = true;
			    $this->cuGroupHasConferenceCategoryModel->setAssociatedConferenceCategories($record->id, $conferenceCategories);

			    if ($this->presenter->isAjax()) {
				    $form->clearValues();
				    $this->redrawControl('cuGroupAddForm');
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
     * @return CuGroupEditForm
     */
	public function createComponentCuGroupEditForm(string $name): CuGroupEditForm
    {
        $form = new CuGroupEditForm($this, $name);

        $form->onError[] = function () {
            $this->redrawControl('cuGroupEditForm');
        };

        $form->onSuccess[] = function (CuGroupEditForm $form) {
		    $formValues = $form->getValuesTransformed();
		    $conferenceCategories = $formValues['conference_categories'] ? explode(" ", $formValues['conference_categories']) : [];
		    unset($formValues['conference_categories']);

		    $this->cuGroupModel->update($formValues);
		    $record = $this->cuGroupModel->find($formValues['id']);
		    $this->cuGroupHasConferenceCategoryModel->setAssociatedConferenceCategories($record->id, $conferenceCategories);
		    $this->template->cuGroupEdited = true;

		    if($this->presenter->isAjax()) {
			    $this->redrawControl('cuGroupEditForm');
		    } else {
		        $this->redirect('this');
            }

		    $this->onEdit($record);
        };

        return $form;
	}

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleDelete(int $id): void
    {
		$record = $this->cuGroupModel->find($id);

		if($record) {
			$record->toArray(); // load the object to be passed to the callback
			$this->cuGroupModel->deleteAssociatedRecords($id);
			$this->template->cuGroupDeleted = true;

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deleteCuGroup');
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
		$cuGroup = $this->cuGroupModel->find($id);

		$conference_categories_res = $this->cuGroupHasConferenceCategoryModel->findAllBy(array('cu_group_id' => $id));
		$conference_category_ids = [];
		foreach($conference_categories_res as $conference_category) $conference_category_ids[] = $conference_category->conference_category_id;

		$this['cuGroupEditForm']->setDefaults($cuGroup);
		$this['cuGroupEditForm']['conference_categories']->setValue(implode(' ', $conference_category_ids));

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('cuGroupEditForm');
		}
	}

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleShowRelatedUsers(int $id): void
    {
		$users = [];
		$rels = $this->submitterHasCuGroupModel->getAllByCuGroupId($id);

		foreach ($rels as $rel) {
			foreach ($rel->related('submitter') as $submitter) {
			    $users[] = $submitter;
            }
		}

		$this->template->usersRelatedToCuGroup = $users;

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('usersRelatedToCuGroup');
		}
	}

    /**
     * @param array|null $params
     */
	public function render(?array $params = []): void
    {
		$this->template->addFormConferenceCategoriesElementId = $this['cuGroupAddForm']['conference_categories']->getHtmlId();
		$this->template->editFormConferenceCategoriesElementId = $this['cuGroupEditForm']['conference_categories']->getHtmlId();

		$this->addDefaultTemplateVars([
			"cuGroupAdded" => false,
			"cuGroupEdited" => false,
			"cuGroupDeleted" => false,
			"usersRelatedToCuGroup" => []
		]);

		parent::render($params);
	}

    /**
     * @return ConferenceCategoryListComponent
     */
	public function createComponentConferenceCategoryListA(): ConferenceCategoryListComponent
    {
        return $this->createComponentConferenceCategoryList();
    }

    /**
     * @return ConferenceCategoryListComponent
     */
	public function createComponentConferenceCategoryListE(): ConferenceCategoryListComponent
    {
        return $this->createComponentConferenceCategoryList();
    }

    /**
     * @return ConferenceCategoryListComponent
     */
	protected function createComponentConferenceCategoryList(): ConferenceCategoryListComponent
    {
		$c = $this->conferenceCategoryListFactory->create();
		$c->setHasControls(true);
		$c->setIsSelectable(true);
		$c->setHasThreeStates(true);
		$c->setHeight('250px');
		return $c;
	}

}