<?php

namespace App\CrudComponents\Reference;

use App\Components\BaseControl;
use Nette\Application\UI\Multiplier;
use \App\CrudComponents\BaseCrudControlsComponent;
use \App\Helpers\ReferenceParser;


class ReferenceCrud extends \App\CrudComponents\BaseCrudComponent {

	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var  \App\Model\Reference */
	protected $referenceModel;

	/** @var  \App\Model\Publication */
	protected $publicationModel;

	/** @var int */
	protected $publicationId;

	/** @var  array */
	protected $actionsAllowed = ['delete', 'add'];

	/** @var array - actions that results in changing model */
	protected $writeActions = ['delete', 'add'];


    /**
     * ReferenceCrud constructor.
     * @param int $publicationId
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\Publication $publicationModel
     * @param \App\Model\Reference $referenceModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param null $name
     */
	public function __construct(
		int $publicationId,
		\Nette\Security\User $loggedUser,
        \App\Model\Publication $publicationModel,
        \App\Model\Reference $referenceModel,
		\Nette\ComponentModel\IContainer $parent = NULL,
        $name = NULL)
    {
        parent::__construct();
        $parent->addComponent($this, $name);

		$this->addDefaultTemplateVars([
			'entityAdded'   =>  false,
			'entityEdited'  =>  false,
			'entityDeleted' =>  false,
		]);

		$this->publicationId = $publicationId;
		$this->referenceModel = $referenceModel;
		$this->publicationModel = $publicationModel;
		$this->loggedUser = $loggedUser;

		$this->onControlsCreate[] = function (BaseCrudControlsComponent &$controlsComponent) {
		    $controlsComponent->addActionAvailable('select');
		};
	}

    /**
     * @param string $name
     * @return ReferenceAddForm
     */
	public function createComponentAddForm(string $name): ReferenceAddForm
    {
		$form = new ReferenceAddForm($this->publicationId, $this->publicationModel, $this, $name);

		$form->onError[] = function () {
			$this->redrawControl('addForm');
		};

		$form->onSuccess[] = function (ReferenceAddForm $form) {
		    if (!$this->isActionAllowed('add')) {
		        return;
            }

            $formValues = $form->getValuesTransformed();
		    $formValues['submitter_id'] = $this->loggedUser->id;
		    $formValues['publication_id'] = $this->publicationId;
		    $record = $this->referenceModel->insert($formValues);

		    if ($record) {
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
     * @return ReferenceSelectForm
     */
    public function createComponentSelectForm(string $name): ReferenceSelectForm
    {
        $form = new ReferenceSelectForm($this->publicationId,$this->publicationModel, $this, $name);

        $form->onError[] = function () {
            $this->redrawControl('selectForm');
        };

        $form->onSuccess[] = function (ReferenceSelectForm $form) {
            $formValues = $form->getValuesTransformed();
            $record = $this->referenceModel->find($formValues['id']);

            if (!empty($record->reference_id)) {
                return;
            }

            $formValues['confirmed'] = 1;
            $record = $this->referenceModel->update($formValues);

            if($record) {
                $this->template->entitySelected = true;

                if ($this->presenter->isAjax()) {
                    $form->clearValues();
                    $this->redrawControl('selectForm');
                } else {
                    $this->redirect('this');
                }

                $this->onEdit($record);
            }
        };

        return $form;
    }

    /**
     * @param string $name
     * @return ReferenceEditForm
     */
    public function createComponentEditForm($name): ReferenceEditForm
    {
        $form = new ReferenceEditForm($this->publicationId,$this->publicationModel, $this, $name);

        $form->onError[] = function () {
            $this->redrawControl('editForm');
        };

        $form->onSuccess[] = function (ReferenceEditForm $form) {
            $formValues = $form->getValuesTransformed();
            $record = $this->referenceModel->find($formValues['id']);

            if (!empty($record->reference_id)) {
                return;
            }

            $parser = new ReferenceParser($formValues['text']);
            $parser->parse();
            $formValues['title'] = $parser->getTitle();

            $record = $this->referenceModel->update($formValues);

            if($record) {
            $this->template->entityEdited = true;

            if ($this->presenter->isAjax()) {
                $form->clearValues();
                $this->redrawControl('editForm');
            } else {
                $this->redirect('this');
            }

            $this->onEdit($record);
            }
        };

        return $form;
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
			$record->toArray(); // load the object to be passed to the callback
			$this->referenceModel->delete($reference_id);
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
     * @throws \Nette\Application\AbortException
     */
	public function handleEdit(int $reference_id): void
    {
        $reference = $this->referenceModel->find($reference_id);
        $this['editForm']->setDefaults($reference); // set up new values

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl('editForm');
        }
    }

    /**
     * @param int $reference_id
     * @throws \Nette\Application\AbortException
     */
    public function handleSelect(int $reference_id): void
    {
        $reference = $this->referenceModel->find($reference_id);
        $this['selectForm']['reference_id']->setItems($this->publicationModel->getPairsForReference($this->publicationId, $reference_id));
        $this['selectForm']->setDefaults($reference); // set up new values

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl('selectForm');
        }
    }

    /**
     * @return Multiplier
     */
    public function createComponentControls(): Multiplier
    {
        $parent = $this;
        $templateFile = dirname($this->getReflection()->getFileName()) . '/controls.latte';

        $callbacks = $this->onControlsCreate;

        return new Multiplier(function ($recordId) use ($parent, $templateFile, $callbacks) {
            $c = new BaseCrudControlsComponent($recordId, $this->getUniqueId(), $templateFile);

            // fill controls template with actions allowed
            $tmp = new \stdClass();
            $this->fillTemplateWithAllowedActions($tmp);
            $record = $this->referenceModel->find($recordId);
            if (!empty($record->text) && $record->confirmed == 0) {
                $tmp->editAllowed = true;
            }
            $c->addTemplateVars(get_object_vars($tmp));

            foreach ($callbacks as $callback) {
                $callback($c);
            }

            return $c;
        });
    }

}
