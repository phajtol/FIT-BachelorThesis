<?php

namespace App\CrudComponents\Attribute;


use App\Components\Publication\PublicationControl;
use App\CrudComponents\BaseCrudComponent;
use App\CrudComponents\BaseCrudControlsComponent;

class AttributeCrud extends BaseCrudComponent {


	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var \App\Model\Attribute */
	protected $attributeModel;

	/** @var \App\Model\AttribStorage */
	protected $attribStorageModel;

	/** @var \App\Model\Publication */
	protected $publicationModel;

	/** @var \App\Model\Author */
	protected $authorModel;


    /**
     * AttributeCrud constructor.
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\Attribute $attributeModel
     * @param \App\Model\AttribStorage $attribStorageModel
     * @param \App\Model\Publication $publicationModel
     * @param \App\Model\Author $authorModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
		\Nette\Security\User $loggedUser,
        \App\Model\Attribute $attributeModel,
        \App\Model\AttribStorage $attribStorageModel,
		\App\Model\Publication $publicationModel,
		\App\Model\Author $authorModel,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
        parent::__construct();
        if ($parent) {
            $parent->addComponent($this, $name);
        }

		$this->addDefaultTemplateVars([
			'attributeAdded' => false,
			'attributeEdited' => false,
			'attributeDeleted' => false,
			'publicationsRelatedToAttribute' => []
		]);

		$this->attributeModel = $attributeModel;
		$this->attribStorageModel = $attribStorageModel;
		$this->publicationModel = $publicationModel;
		$this->authorModel = $authorModel;
		$this->loggedUser = $loggedUser;

		$this->onControlsCreate[] = function (BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedPublications');
		};
	}

    /**
     * @param $name
     * @return AttributeAddForm
     */
	public function createComponentAttributeAddForm (string $name): AttributeAddForm
    {
        $form = new AttributeAddForm($this->attributeModel, $this, $name);

        $form->onError[] = function () {
            $this->redrawControl('attributeAddForm');
        };

        $form->onSuccess[] = function(AttributeAddForm $form) {
            $formValues = $form->getValuesTransformed();

		    $formValues['submitter_id'] = $this->loggedUser->id;

		    $record = $this->attributeModel->insert($formValues);

		    if ($record) {
			    $this->template->attributeAdded = true;

			    if ($this->presenter->isAjax()) {
				    $form->clearValues();
				    $this->redrawControl('attributeAddForm');
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
     * @return AttributeEditForm
     */
	public function createComponentAttributeEditForm(string $name): AttributeEditForm
    {
        $form = new AttributeEditForm($this, $name);

        $form->onError[] = function () {
            $this->redrawControl('attributeEditForm');
        };

        $form->onSuccess[] = function (AttributeEditForm $form) {
            $formValues = $form->getValuesTransformed();

		    $formValues['submitter_id'] = $this->loggedUser->id;

		    $this->attributeModel->update($formValues);
		    $record = $this->attributeModel->find($formValues['id']);

		    $this->template->attributeEdited = true;

		    if ($this->presenter->isAjax()) {
			    $this->redrawControl('attributeEditForm');
		    } else {
		        $this->redirect('this');
            }

		    $this->onEdit($record);
        };

        return $form;
	}

    /**
     * @return PublicationControl
     */
	public function createComponentPublication(): PublicationControl
    {
        return new PublicationControl();
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleDelete(int $id): void
    {
		$record = $this->attributeModel->find($id);

		if($record) {
			$record->toArray(); // load the object to be passed to the callback

			$this->attributeModel->deleteAssociatedRecords($id);

			$this->template->attributeDeleted = true;

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deleteAttribute');
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
		$attribute = $this->attributeModel->find($id);

		$this['attributeEditForm']->setDefaults($attribute); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('attributeEditForm');
		}

	}

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleShowRelatedPublications(int $id): void
    {
        $publicationIds = $this->attribStorageModel->getPublicationsByAttribute($id);
        $publications = $this->publicationModel->getMultiplePubInfoByIds($publicationIds);
        $authorsByPubId = [];

        foreach ($publications as $publication) {
            $authorsByPubId[$publication->id] = $this->authorModel->getAuthorsNamesByPubIdPure($publication->id);
        }

		$this->template->publicationsRelatedToAttribute = $publications;
        $this->template->authorsByPubId = $authorsByPubId;

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('publicationsRelatedToAttribute');
		}
	}

}