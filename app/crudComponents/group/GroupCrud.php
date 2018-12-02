<?php

namespace App\CrudComponents\Group;


class GroupCrud extends \App\CrudComponents\BaseCrudComponent {

	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var  \App\Model\Group */
	protected $groupModel;

	/** @var  \App\Model\GroupHasPublication */
	protected $groupHasPublicationModel;


    /**
     * GroupCrud constructor.
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\Group $groupModel
     * @param \App\Model\GroupHasPublication $groupHasPublicationModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
		\Nette\Security\User $loggedUser,
        \App\Model\Group $groupModel,
        \App\Model\GroupHasPublication $groupHasPublicationModel,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->addDefaultTemplateVars([
			'entityAdded'   =>  false,
			'entityEdited'  =>  false,
			'entityDeleted' =>  false,
			'relatedPublications'   =>  []
		]);

		$this->groupModel = $groupModel;
		$this->groupHasPublicationModel = $groupHasPublicationModel;
		$this->loggedUser = $loggedUser;

		$this->onControlsCreate[] = function (\App\CrudComponents\BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedPublications');
		};
	}

    /**
     * @param string $name
     * @return GroupAddForm
     */
	public function createComponentAddForm(string $name): GroupAddForm
    {
        $form = new GroupAddForm($this->groupModel, $this, $name);

        $form->onError[] = function () {
            $this->redrawControl('addForm');
        };

        $form->onSuccess[] = function (GroupAddForm $form) {
		    if (!$this->isActionAllowed('add')) {
		        return;
            }

		    $formValues = $form->getValuesTransformed();
		    $record = $this->groupModel->insert($formValues);

		    if($record) {
			    $this->template->entityAdded = true;

			    if ($this->presenter->isAjax()) {
				    $form->clearValues();
				    $this->redrawControl('addForm');
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
     * @return GroupEditForm
     */
	public function createComponentEditForm(string $name): GroupEditForm
    {
        $form = new GroupEditForm($this, $name);

        $form->onError[] = function () {
            $this->redrawControl('editForm');
        };

        $form->onSuccess[] = function (GroupEditForm $form) {
		    if (!$this->isActionAllowed('edit')) {
		        return;
            }

		    $formValues = $form->getValuesTransformed();
		    $this->groupModel->update($formValues);
		    $record = $this->groupModel->find($formValues['id']);
		    $this->template->entityEdited = true;

		    if ($this->presenter->isAjax()) {
			    $this->redrawControl('editForm');
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
		if (!$this->isActionAllowed('delete')) {
		    return;
        }

		$record = $this->groupModel->find($id);

		if($record) {
			$record->toArray(); // load the object to be passed to the callback
			$this->groupModel->deleteAssociatedRecords($id);
			$this->template->entityDeleted = true;

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deleteEntity');
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
		if (!$this->isActionAllowed('edit')) {
		    return;
        }

		$group = $this->groupModel->find($id);
		$this['editForm']->setDefaults($group); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('editForm');
		}
	}

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleShowRelatedPublications(int $id): void
    {
		$this->template->publicationsRelated = $this->groupHasPublicationModel->findAllBy(["group_id" => $id]);

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('relatedPublications');
		}
	}

}