<?php

namespace App\CrudComponents\Annotation;


class AnnotationCrud extends \App\CrudComponents\BaseCrudComponent {



	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var  \App\Model\Annotation */
	protected $annotationModel;

	/** @var int */
	protected $publicationId;


    /**
     * AnnotationCrud constructor.
     * @param int $publicationId
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\Annotation $annotationModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
		int $publicationId,
		\Nette\Security\User $loggedUser,
        \App\Model\Annotation $annotationModel,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
        parent::__construct($parent, $name);

		$this->addDefaultTemplateVars([
			'entityAdded'   =>  false,
			'entityEdited'  =>  false,
			'entityDeleted' =>  false,
		]);

		$this->publicationId = $publicationId;
		$this->annotationModel = $annotationModel;
		$this->loggedUser = $loggedUser;

		$this->onControlsCreate[] = function (\App\CrudComponents\BaseCrudControlsComponent &$controlsComponent) {
			//
		};
	}

    /**
     * @param string $name
     * @return AnnotationAddForm
     */
	public function createComponentAddForm (string $name): AnnotationAddForm
    {
        $form = new AnnotationAddForm($this, $name);

        $form->onError[] = function () {
            $this->redrawControl('addForm');
        };

        $form->onSuccess[] = function (AnnotationAddForm $form) {
		    if (!$this->isActionAllowed('add')) {
		        return;
            }

		    $formValues = $form->getValuesTransformed();

		    $formValues['submitter_id'] = $this->loggedUser->id;
		    $formValues['publication_id'] = $this->publicationId;
		    $formValues['date'] = new \Nette\Utils\DateTime();

		    $record = $this->annotationModel->insert($formValues);

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
     * @return AnnotationEditForm
     */
	public function createComponentEditForm(string $name): AnnotationEditForm
    {
		$form = new AnnotationEditForm($this, $name);

		$form->onError[] = function () {
			$this->redrawControl('editForm');
		};

		$form->onSuccess[] = function (AnnotationEditForm $form) {
		    if(!$this->isActionAllowed('edit')) {
                return;
            }

            $formValues = $form->getValuesTransformed();

		    $formValues['submitter_id'] = $this->loggedUser->id;
		    $formValues['date'] = new \Nette\Utils\DateTime();

		    $this->annotationModel->update($formValues);
		    $record = $this->annotationModel->find($formValues['id']);

		    $this->template->entityEdited = true;

		    if($this->presenter->isAjax()) {
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

		$record = $this->annotationModel->find($id);

		if ($record) {
			$record->toArray(); // load the object to be passed to the callback
			$this->annotationModel->deleteAssociatedRecords($id);
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
		$annotation = $this->annotationModel->find($id);
		$this['editForm']->setDefaults($annotation); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('editForm');
		}

	}

}