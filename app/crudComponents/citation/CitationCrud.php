<?php

namespace App\CrudComponents\Citation;


class CitationCrud extends \App\CrudComponents\BaseCrudComponent {

	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var  \App\Model\Reference */
	protected $referenceModel;

	/** @var  \App\Model\Publication */
	protected $publicationModel;

	/** @var int */
	protected $publicationId;

    /** @var  array */
	protected $actionsAllowed = ['delete'];

	/** @var array actions that results in changing model */
	protected $writeActions = ['delete'];


    /**
     * CitationCrud constructor.
     * @param int $publicationId
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\Publication $publicationModel
     * @param \App\Model\Reference $referenceModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
    public function __construct(
		int $publicationId,
		\Nette\Security\User $loggedUser,
        \App\Model\Publication $publicationModel,
        \App\Model\Reference $referenceModel,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
        parent::__construct($parent, $name);

        $this->addDefaultTemplateVars([
    	    'entityDeleted' =>  false,
        ]);

        $this->publicationId = $publicationId;
        $this->referenceModel = $referenceModel;
        $this->publicationModel = $publicationModel;
        $this->loggedUser = $loggedUser;
	}

    /**
     * @param int $reference_id
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete(int $reference_id): void
    {
		if (!$this->isActionAllowed('delete')) {
		    return;
        }

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

    /**
     * @param int $reference_id
     */
    public function handleEdit(int $reference_id): void
    {
        //wtf
    }
}
