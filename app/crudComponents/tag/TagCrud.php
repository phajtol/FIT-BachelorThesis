<?php
namespace App\CrudComponents\Tag;


class TagCrud extends \App\CrudComponents\BaseCrudComponent {



	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var  \App\Model\Tag */
	protected $tagModel;


	public function __construct(
		\Nette\Security\User $loggedUser, \App\Model\Tag $tagModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL
	) {
		parent::__construct($parent, $name);

		$this->addDefaultTemplateVars(array(
			'tagAdded'   =>  false,
			'tagEdited'  =>  false,
			'tagDeleted' =>  false,
		));

		$this->tagModel = $tagModel;
		$this->loggedUser = $loggedUser;

		$this->onControlsCreate[] = function(\App\CrudComponents\BaseCrudControlsComponent &$controlsComponent) {
			//
		};
	}

	public function createComponentTagForm($name){
      if(!$this->isActionAllowed('edit') && !$this->isActionAllowed('add')) {
          return null;
      }
      $form = new TagForm($this, $name);
      $form->onError[] = function(){
              $this->redrawControl('tagForm');
      };
      $form->onSuccess[] = function(TagForm $form) {
      		$formValues = $form->getValuesTransformed();

          $formValues['submitter_id'] = intval($this->loggedUser->id);

          if (empty($formValues['id'])) {
              $this->template->tagAdded = true;
              unset($formValues['id']);
              $record = $this->tagModel->insert($formValues);
              $this->onAdd($record);
      		} else {
        			$this->template->tagEdited = true;
        			$this->tagModel->update($formValues);
        			$record = $this->tagModel->find($formValues['id']);
              $this->onEdit($record);
      		}
          if (!$this->presenter->isAjax()) {
      		   $this->presenter->redirect('this');
      		} else {
              $form->clearValues();
      	      $this->redrawControl('tagForm');
      		}
      };
      return $form;
	}

	public function handleDelete($id) {
		if(!$this->isActionAllowed('delete')) return;

		$record = $this->tagModel->find($id);
		if($record) {

			$record->toArray(); // load the object to be passed to the callback

			$count = $this->tagModel
                                ->findAllBy(array("id" => $record->id, "submitter_id" => $this->loggedUser->id))
                                ->delete();
			$this->template->tagDeleted = true;

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deleteTag');
			}

			$this->onDelete($record);
		}
	}
  public function handleAdd() {
		if(!$this->isActionAllowed('add')) return;

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('tagForm');
		}
	}

	public function handleEdit($id) {
		if(!$this->isActionAllowed('edit')) return;

		$tag = $this->tagModel->find($id);

		$this["tagForm"]->setDefaults($tag); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('tagForm');
		}

	}
  public function createComponentAddButton(){
		$sc = parent::createComponentAddButton();
		$sc->template->addLink =  $this->link('add!');
		return $sc;
	}
}
