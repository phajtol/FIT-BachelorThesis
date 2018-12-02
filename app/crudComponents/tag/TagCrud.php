<?php

namespace App\CrudComponents\Tag;

use App\Components\StaticContentComponent;


class TagCrud extends \App\CrudComponents\BaseCrudComponent {

	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var  \App\Model\Tag */
	protected $tagModel;

    /**
     * TagCrud constructor.
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\Tag $tagModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
		\Nette\Security\User $loggedUser,
        \App\Model\Tag $tagModel,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->addDefaultTemplateVars([
			'tagAdded'   =>  false,
			'tagEdited'  =>  false,
			'tagDeleted' =>  false,
		]);

		$this->tagModel = $tagModel;
		$this->loggedUser = $loggedUser;

		$this->onControlsCreate[] = function (\App\CrudComponents\BaseCrudControlsComponent &$controlsComponent) {
			//
		};
	}

    /**
     * @param $name
     * @return TagForm|null
     */
	public function createComponentTagForm($name): ?TagForm
    {
        if (!$this->isActionAllowed('edit') && !$this->isActionAllowed('add')) {
            return null;
        }

        $form = new TagForm($this, $name);

        $form->onError[] = function () {
            $this->redrawControl('tagForm');
        };

        $form->onSuccess[] = function (TagForm $form) {
            $formValues = $form->getValuesTransformed();

            $formValues['submitter_id'] = intval($this->loggedUser->id);

            if (empty($formValues['id'])) {
                $this->template->tagAdded = true;
                unset($formValues['id']);
                $record = $this->tagModel->insert($formValues);
                $this->onAdd($record);
            } else {
                $this->tagModel->update($formValues);
                $this->template->tagEdited = true;
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

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleDelete($id): void
    {
		if (!$this->isActionAllowed('delete')) {
		    return;
        }

		$record = $this->tagModel->find($id);

		if ($record) {
			$record->toArray(); // load the object to be passed to the callback

			$this->tagModel->findAllBy(array("id" => $record->id, "submitter_id" => $this->loggedUser->id))->delete();
			$this->template->tagDeleted = true;

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deleteTag');
			}

			$this->onDelete($record);
		}
	}

    /**
     * @throws \Nette\Application\AbortException
     */
    public function handleAdd(): void
    {
		if (!$this->isActionAllowed('add')) {
		    return;
        }

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('tagForm');
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

		$tag = $this->tagModel->find($id);

		$this['tagForm']->setDefaults($tag); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('tagForm');
		}

	}

    /**
     * @return StaticContentComponent
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function createComponentAddButton(): StaticContentComponent
    {
		$sc = parent::createComponentAddButton();
		$sc->template->addLink = $this->link('add!');
		return $sc;
	}
}
