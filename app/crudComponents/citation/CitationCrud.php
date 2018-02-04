<?php

namespace App\CrudComponents\Citation;


class CitationCrud extends \App\CrudComponents\BaseCrudComponent {

	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var  \App\Model\Reference */
	protected $referenceModel;

	/** @var  \App\Model\Publication */
	protected $publicationModel;

	protected $publicationId;

  /** @var  array */
	protected $actionsAllowed = array('delete');

	/** @var array actions that results in changing model */
	protected $writeActions = array('delete');

  public function __construct(
		$publicationId,
		\Nette\Security\User $loggedUser, \App\Model\Publication $publicationModel, \App\Model\Reference $referenceModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL
	) {
    parent::__construct($parent, $name);

    $this->addDefaultTemplateVars(array(
    	'entityDeleted' =>  false,
    ));

    $this->publicationId = $publicationId;
    $this->referenceModel = $referenceModel;
    $this->publicationModel = $publicationModel;
    $this->loggedUser = $loggedUser;
	}

  public function handleDelete($reference_id) {
		if(!$this->isActionAllowed('delete')) return;

		$record = $this->referenceModel->find($reference_id);
		if($record) {
			$this->referenceModel->delete($record->id);
			$this->template->entityDeleted = true;
			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deleteEntity');
			}
			$this->onDelete($record);
		}
  }

  public function handleEdit($reference_id) {

  }
}
