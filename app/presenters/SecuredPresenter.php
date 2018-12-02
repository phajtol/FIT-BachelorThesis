<?php

namespace App\Presenters;


abstract class SecuredPresenter extends BasePresenter {

    /**
     * @throws \Nette\Application\AbortException
     */
    protected function startup(): void
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
        }

        if (!$this->user->isAllowed($this->name . ':' . $this->action)) {
            $this->flashMessage("Access denied, you don't have permission to access!", 'alert-danger');
            $this->presenter->redirect('Homepage:');
        }
    }

}
