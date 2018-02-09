<?php

namespace App\CrudComponents\PublicationTag;


class PublicationTagCrud extends \App\CrudComponents\BaseCrudComponent {


	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var  \App\Model\PublicationHasTag */
	protected $publicationHasTagModel;

  /** @var  \App\Model\Tag */
	protected $tagModel;

	protected $publicationId;

	public function __construct(
		$publicationId,
		\Nette\Security\User $loggedUser, \App\Model\Tag $tagModel,
    \App\Model\PublicationHasTag $publicationHasTagModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL
	) {
		parent::__construct($parent, $name);

		$this->addDefaultTemplateVars(array(
			'publicationTagAdded'   =>  false,
			'publicationTagEdited'  =>  false,
			'publicationTagDeleted' =>  false,
		));

		$this->publicationId = $publicationId;
		$this->publicationHasTagModel = $publicationHasTagModel;
    $this->tagModel = $tagModel;
		$this->loggedUser = $loggedUser;

		$this->onControlsCreate[] = function(\App\CrudComponents\BaseCrudControlsComponent &$controlsComponent) {
			//
		};
	}

	public function createComponentPublicationTagForm($name){
            $form = new PublicationTagForm($this->tagModel, $this->loggedUser, $this, $name);
            $form->onError[] = function(){
                    $this->redrawControl('publicationTagForm');
            };
            $form->onSuccess[] = function(PublicationTagForm $form) {
		if(!$this->isActionAllowed('add')) return;

		$formValues = $form->getValuesTransformed();

		$formValues['publication_id'] = $this->publicationId;

		$record = $this->publicationHasTagModel->insert($formValues);

		if($record) {
			$this->template->publicationTagAdded = true;

			if ($this->presenter->isAjax()) {
				$form->clearValues();
				$this->redrawControl('publicationTagForm');
			} else $this->redirect('this');

			$this->onAdd($record);
		}
            };

	}

	public function handleDelete($id) {
		if(!$this->isActionAllowed('delete')) return;

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

  public function handleEdit($id) {

  }


}
