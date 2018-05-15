<?php

namespace App\Presenters;

use Nette,
    App\Model;

abstract class SecuredPresenter extends BasePresenter {

  protected function startup() {
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
