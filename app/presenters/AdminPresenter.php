<?php

namespace App\Presenters;

use Nette,
    App\Model,
    \VisualPaginator,
    Nette\Diagnostics\Debugger;

class AdminPresenter extends SecuredPresenter {

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }

    protected function startup() {
        parent::startup();
    }

    public function actionDefault() {
        
    }

    public function renderDefault() {
        
    }

    public function actionShowUnconfirmed($sort, $order, $keywords) {
        Debugger::fireLog('actionShowUnconfirmed');
        $this->drawAllowed = false;
        $this->template->publicationDeleted = false;
        $this->drawPublicationUnconfirmed();
    }

    protected function createComponentAdminShowUnconfirmedForm($name) {
        $form = new \AdminShowUnconfirmedForm($this, $name);
        $form->onSuccess[] = $this->adminShowUnconfirmedFormSucceeded;
        return $form;
    }

    public function adminShowUnconfirmedFormSucceeded($form) {

        Debugger::fireLog('adminShowUnconfirmedFormSucceeded()');

        $formValues = $form->getValues();

        foreach ($formValues as $key => $value) {
            $name = explode('_', $key);
            if ($value == 'TRUE') {
                $this->context->Publication->update(array('id' => $name[1], 'confirmed' => 1));
            }
        }

        $this->drawAllowed = true;

        $this->flashMessage('Operation has been completed successfully.', 'alert-success');

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl('publicationShowAllRecords');
            $this->redrawControl('flashMessages');
        }
    }

    public function actionSettings() {
        $this->template->generalSettingsEdited = false;
        $this->template->generalSettings = $this->context->GeneralSettings->findOneBy(array('id' => 1));
    }

    protected function createComponentPublicationEditGeneralSettingsForm($name) {
        $form = new \PublicationAddNewGeneralSettingsForm($this, $name);
        $form->onSuccess[] = $this->publicationEditGeneralSettingsFormSucceeded;
        $form->onError[] = $this->publicationEditGeneralSettingsFormError;
        return $form;
    }

    public function publicationEditGeneralSettingsFormError($form) {
        $this->redrawControl('publicationEditGeneralSettingsForm');
    }

    public function handleEditGeneralSettings() {

        $this->drawAllowed = false;

        $generalSettings = $this->context->GeneralSettings->findOneBy(array('id' => 1));

        $this["publicationEditGeneralSettingsForm"]->setDefaults($generalSettings); // set up new values

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl('publicationEditGeneralSettingsForm');
        }
    }

    public function publicationEditGeneralSettingsFormSucceeded($form) {

        Debugger::fireLog('publicationEditGeneralSettingsFormSucceeded');

        $formValues = $form->getValues();

        $this->drawAllowed = false;

        Debugger::fireLog($form->values->id);
        Debugger::fireLog($this->action);

        $this->context->GeneralSettings->update($formValues);

        $this->template->generalSettingsEdited = true;

        $generalSettings = $this->context->GeneralSettings->find($form->values->id);
        $this->template->generalSettings = $generalSettings;

        $this->flashMessage('Operation has been completed successfully.', 'alert-success');

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $form->setValues(array(), TRUE);
            $this->redrawControl('publicationEditGeneralSettingsForm');
            $this->redrawControl('settingsShowSettings');
            $this->redrawControl('flashMessages');
        }
    }

}
