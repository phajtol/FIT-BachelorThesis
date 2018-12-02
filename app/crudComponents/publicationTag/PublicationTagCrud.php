<?php

namespace App\CrudComponents\PublicationTag;


class PublicationTagCrud extends \App\CrudComponents\BaseCrudComponent {


	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var  \App\Model\PublicationHasTag */
	protected $publicationHasTagModel;

    /** @var  \App\Model\Tag */
	protected $tagModel;

	/** @var int */
	protected $publicationId;


    /**
     * PublicationTagCrud constructor.
     * @param int $publicationId
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\Tag $tagModel
     * @param \App\Model\PublicationHasTag $publicationHasTagModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
		int $publicationId,
		\Nette\Security\User $loggedUser,
        \App\Model\Tag $tagModel,
        \App\Model\PublicationHasTag $publicationHasTagModel,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->addDefaultTemplateVars([
			'publicationTagAdded'   =>  false,
			'publicationTagEdited'  =>  false,
			'publicationTagDeleted' =>  false,
		]);

		$this->publicationId = $publicationId;
		$this->publicationHasTagModel = $publicationHasTagModel;
        $this->tagModel = $tagModel;
		$this->loggedUser = $loggedUser;

		$this->onControlsCreate[] = function (\App\CrudComponents\BaseCrudControlsComponent &$controlsComponent) {
			//
		};
	}

    /**
     * @param string $name
     * @return PublicationTagForm
     */
	public function createComponentPublicationTagForm(string $name): PublicationTagForm
    {
        $form = new PublicationTagForm($this->tagModel, $this->loggedUser, $this, $name);

        $form->onError[] = function () {
            $this->redrawControl('publicationTagForm');
        };

        $form->onSuccess[] = function (PublicationTagForm $form) {
		    if (!$this->isActionAllowed('add')) {
		        return;
            }

		    $formValues = $form->getValuesTransformed();
		    $formValues['publication_id'] = $this->publicationId;
		    $record = $this->publicationHasTagModel->insert($formValues);

		    if($record) {
			    $this->template->publicationTagAdded = true;

			    if ($this->presenter->isAjax()) {
				    $form->clearValues();
				    $this->redrawControl('publicationTagForm');
			    } else {
			        $this->redirect('this');
                }

			    $this->onAdd($record);
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
		if (!$this->isActionAllowed('delete')) {
		    return;
        }

		$records = $this->publicationHasTagModel->findAllBy(["tag_id" => $id]);

		if($records) {
			$this->publicationHasTagModel->deleteByTagId($id);
			$this->template->publicationTagDeleted = true;

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deletePublicationTag');
			}

			$this->onDelete();
		}
	}

    /**
     * @param int $id
     */
    public function handleEdit(int $id): void
    {
        //wtf
    }

}
