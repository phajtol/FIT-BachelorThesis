<?php

namespace App\CrudComponents\Reference;


class ReferenceCrud extends \App\CrudComponents\BaseCrudComponent {



	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var  \App\Model\Reference */
	protected $referenceModel;

	/** @var  \App\Model\Publication */
	protected $publicationModel;
        
	protected $publicationId;
        
        /** @var  array */
	protected $actionsAllowed = array('delete', 'add');

	/** @var array actions that results in changing model */
	protected $writeActions = array('delete', 'add');

	public function __construct(
		$publicationId,
		\Nette\Security\User $loggedUser, \App\Model\Publication $publicationModel, \App\Model\Reference $referenceModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL
	) {
		parent::__construct($parent, $name);

		$this->addDefaultTemplateVars(array(
			'entityAdded'   =>  false,
			'entityEdited'  =>  false,
			'entityDeleted' =>  false,
		));

		$this->publicationId = $publicationId;
		$this->referenceModel = $referenceModel;
                $this->publicationModel = $publicationModel;
		$this->loggedUser = $loggedUser;

                $this->onControlsCreate[] = function(\App\CrudComponents\BaseCrudControlsComponent &$controlsComponent) {
			//
		};
	}

	public function createComponentAddForm($name){
		$form = new ReferenceAddForm($this->publicationId,$this->publicationModel, $this, $name);
		$form->onError[] = function(){
			$this->redrawControl('addForm');
		};
		$form->onSuccess[] = function(ReferenceAddForm $form) {
                    if(!$this->isActionAllowed('add')) return;

                    $formValues = $form->getValuesTransformed();

                    $formValues['submitter_id'] = $this->loggedUser->id;
                    $formValues['publication_id'] = $this->publicationId;

                    $record = $this->referenceModel->insert($formValues);

                    if($record) {
                            $this->template->entityAdded = true;

                            if ($this->presenter->isAjax()) {
                                    $form->clearValues();
                                    $this->redrawControl('addForm');
                            } else $this->redirect('this');

                            $this->onAdd($record);
                    }
            };
        }
        public function handleDelete($id) {
		if(!$this->isActionAllowed('delete')) return;

		$record = $this->referenceModel->find($id);
		if($record) {

			$record->toArray(); // load the object to be passed to the callback

			$this->referenceModel->delete($id);

			$this->template->entityDeleted = true;

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deleteEntity');
			}

			$this->onDelete($record);
		}
	}
        
        public function handleEdit($id) {
            
        }

	
}