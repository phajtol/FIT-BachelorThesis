<?php

namespace App\Presenters;

use App\Forms\BaseForm;
use App\Model;


class ReferencePresenter extends SecuredPresenter {

    /** @var  Model\Publication @inject */
    public $publicationModel;

    /** @var  Model\Reference @inject */
    public $referenceModel;


    /**
     * @return \App\Forms\BaseForm
     */
    public function createComponentAddListForm(): BaseForm
    {
        $form = new BaseForm();

        $form->addTextArea("references", "Referenced publications",null,20)
            ->setRequired('Referenced publications are required.');

        $form->addHidden("publication_id");
        $form->addSubmit('send', 'Add');

        $form->onError[] = function () {
            $this->redrawControl('addForm');
        };

	    $form->onSuccess[] = function (\Nette\Application\UI\Form $form) {
            $vals = $form->getValues();
            $count = $this->referenceModel->insertList($vals['references'], $vals['publication_id'], $this->user->id);
            $this->flashMessage($count." references added", 'alert-success');
            $this->redirect("Publication:showpub#references", $vals['publication_id']);
        };

        return $form;
    }

    /**
     * @param int $publication_id
     * @throws \Nette\Application\BadRequestException
     */
    public function actionAddlist(int $publication_id) {
        $publication = $this->publicationModel->find($publication_id);

        if (empty($publication)) {
            throw new \Nette\Application\BadRequestException;
        }

        $this->template->publication = $publication;
        $this['addListForm']['publication_id']->setValue($publication->id);
    }
}
