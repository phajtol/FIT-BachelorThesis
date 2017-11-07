<?php

namespace App\Presenters;

use Nette,
    App\Model,
    Nette\Diagnostics\Debugger,
    App\Helpers;

class ReferencePresenter extends SecuredPresenter {

    
    /** @var  Model\Publication
     *  @autowire
     */
    protected $publicationModel;

    /** @var  Model\Reference
     *  @autowire
     */
    protected $referenceModel;
    
    public function createComponentAddListForm() {
        $form = new \App\Forms\BaseForm();
        
        $form->addTextArea("references", "Referenced publications",null,20)
            ->setRequired('Referenced publications are required.');
        $form->addHidden("publication_id");
        $form->addSubmit('send', 'Add');

        $form->onError[] = function(){
            $this->redrawControl('addForm');
        };
	$form->onSuccess[] = function(\Nette\Application\UI\Form $form) {
            $vals = $form->getValues();
            $count = $this->referenceModel->insertList($vals['references'], $vals['publication_id'], $this->user->id);
            $this->flashMessage($count." references added", 'alert-success');
            $this->redirect("Publication:showpub#references", $vals['publication_id']);
        };
        return $form;
    }
    
    public function actionAddlist($publication_id) {
        $publication = $this->publicationModel->find($publication_id);
        if (empty($publication)) {
            throw new \Nette\Application\BadRequestException;
        }
        $this->template->publication = $publication;
        $this['addListForm']['publication_id']->setValue($publication->id);
    }
}
