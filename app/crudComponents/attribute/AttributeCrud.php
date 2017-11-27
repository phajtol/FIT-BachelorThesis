<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 27.3.2015
 * Time: 23:11
 */

namespace App\CrudComponents\Attribute;


use App\CrudComponents\BaseCrudComponent;
use App\CrudComponents\BaseCrudControlsComponent;

class AttributeCrud extends BaseCrudComponent {


	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var \App\Model\Attribute */
	protected $attributeModel;

	/** @var \App\Model\AttribStorage */
	protected $attribStorageModel;


	public function __construct(

		\Nette\Security\User $loggedUser, \App\Model\Attribute $attributeModel, \App\Model\AttribStorage $attribStorageModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL
	) {
		parent::__construct($parent, $name);

		$this->addDefaultTemplateVars(array(
			'attributeAdded' => false                     ,
			'attributeEdited' => false                    ,
			'attributeDeleted' => false                   ,
			'publicationsRelatedToAttribute' => array()
		));

		$this->attributeModel = $attributeModel;
		$this->attribStorageModel = $attribStorageModel;
		$this->loggedUser = $loggedUser;

		$this->onControlsCreate[] = function(BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedPublications');
		};
	}

	public function createComponentAttributeAddForm($name){
            $form = new AttributeAddForm($this->attributeModel, $this, $name);
            $form->onError[] = function(){
                    $this->redrawControl('attributeAddForm');
            };
            $form->onSuccess[] = function(AttributeAddForm $form) {
            $formValues = $form->getValuesTransformed();

		$formValues['submitter_id'] = $this->loggedUser->id;

		$record = $this->attributeModel->insert($formValues);

		if($record) {
			$this->template->attributeAdded = true;

			if ($this->presenter->isAjax()) {
				$form->clearValues();
				$this->redrawControl('attributeAddForm');
			} else $this->redirect('this');

			$this->onAdd($record);
		}
            };
         
	}

	public function createComponentAttributeEditForm($name){
            $form = new AttributeEditForm($this, $name);
            $form->onError[] = function(){
                    $this->redrawControl('attributeEditForm');
            };
            $form->onSuccess[] = function(AttributeEditForm $form) {
                $formValues = $form->getValuesTransformed();

		$formValues['submitter_id'] = $this->loggedUser->id;

		$this->attributeModel->update($formValues);
		$record = $this->attributeModel->find($formValues['id']);

		$this->template->attributeEdited = true;

		if($this->presenter->isAjax()) {
			$this->redrawControl('attributeEditForm');
		} else $this->redirect('this');

		$this->onEdit($record);
            };
	}

	public function handleDelete($id) {
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

	public function handleEdit($id) {
		$attribute = $this->attributeModel->find($id);

		$this["attributeEditForm"]->setDefaults($attribute); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('attributeEditForm');
		}

	}

	public function handleShowRelatedPublications($id) {
		$this->template->publicationsRelatedToAttribute =
			$this->attribStorageModel->findAllBy(array("attributes_id" => $id));

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('publicationsRelatedToAttribute');
		}
	}

}