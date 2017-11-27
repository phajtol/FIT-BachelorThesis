<?php

namespace App\Presenters;

use Nette,
    App\Model,
    \VisualPaginator,
    Nette\Diagnostics\Debugger;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class UserPresenter extends SecuredPresenter {

    /**
     * @var \App\Services\Authenticators\LoginPassAuthenticator
     */
    protected $loginPassAuthenticator;

    /**
     * @var Model\Submitter
     */
    protected $submitterModel;

    /**
     * @var Model\Annotation
     */
    protected $annotationModel;

    /**
     * @var Model\UserSettings
     */
    protected $userSettingsModel;

    /**
     * @var \App\Services\Authenticators\BaseAuthenticator
     */
    protected $baseAuthenticator;

    /**
     * @var \App\Factories\IAnnotationCrudFactory
     */
    protected $annotationCrudFactory;

    protected $userPasswordChangeFormEnabled = false;


    /**
     * @param \App\Services\Authenticators\LoginPassAuthenticator $loginPassAuthenticator
     */
    public function injectLoginPassAuthenticator(\App\Services\Authenticators\LoginPassAuthenticator $loginPassAuthenticator)
    {
        if(!$this->loginPassAuthenticator)
            $this->loginPassAuthenticator = $loginPassAuthenticator;
    }

    /**
     * @param \App\Services\Authenticators\BaseAuthenticator $baseAuthenticator
     */
    public function injectBaseAuthenticator(\App\Services\Authenticators\BaseAuthenticator $baseAuthenticator) {
        $this->baseAuthenticator = $baseAuthenticator;
    }

    /**
     * @param Model\Submitter $submitterModel
     */
    public function injectSubmitterModel(Model\Submitter $submitterModel)
    {
        if(!$this->submitterModel)
            $this->submitterModel = $submitterModel;
    }

    /**
     * @param Model\Annotation $annotationModel
     */
    public function injectAnnotationModel(Model\Annotation $annotationModel)
    {
        $this->annotationModel = $annotationModel;
    }

    /**
     * @param Model\UserSettings $userSettingsModel
     */
    public function injectUserSettingsModel(Model\UserSettings $userSettingsModel)
    {
        $this->userSettingsModel = $userSettingsModel;
    }

    /**
     * @param \App\Factories\IAnnotationCrudFactory $annotationCrudFactory
     */
    public function injectAnnotationCrudFactory(\App\Factories\IAnnotationCrudFactory $annotationCrudFactory) {
        $this->annotationCrudFactory = $annotationCrudFactory;
    }



    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }

    public function actionDefault() {
        
    }

    public function renderDefault() {
        
    }

    public function actionShow($sort, $order, $keywords) {

        $this->drawAllowed = true;

        $this->template->passwordChanged = false;
        $this->template->userSettingsEdited = false;
        $this->template->userInfo = false;
        $this->template->userAdded = false;
        $this->template->userEdited = false;
        $this->template->userDeleted = false;
        $this->template->userRelated = array();

        $this->userPasswordChangeFormEnabled =
            ($this->baseAuthenticator->getUserAuthenticationMethod($this->user->id) == \App\Services\Authenticators\BaseAuthenticator::AUTH_LOGIN_PASS);

        $submitter = $this->submitterModel->find($this->user->id);

        if (!$submitter) {
            $this->error('User not found');
        }

        $this->id = $this->user->id;

        $annotations = $this->annotationModel->findAllBy(array('submitter_id' => $this->user->id))->order("id ASC");

        $userSettings = $this->userSettingsModel->findOneBy(array('submitter_id' => $this->user->id));

        $this->template->submitter = $submitter;
        $this->template->annotations = $annotations;
        $this->template->userSettings = $userSettings;

        $this->template->annotationAdded = false;
        $this->template->annotationEdited = false;
        $this->template->annotationDeleted = false;

        $this->template->publicationDeleted = false;
    }

    protected function createComponentUserPasswordChangeForm($name) {
        $user = $this->submitterModel->find($this->user->id);

        $form = new \UserPasswordChangeForm($this->loginPassAuthenticator, $user, $this, $name);
        if($this->userPasswordChangeFormEnabled) {
            $form->onSuccess[] = function($form) {

                $user = $this->submitterModel->find($this->user->id);

                $this->loginPassAuthenticator->associateLoginPasswordToUser($this->user->id, $user->nickname, $form['pass']->getValue());

                $this->flashMessage('Your Password has been changed successfully.', 'alert-success');

                $this->template->passwordChanged = true;

                if (!$this->presenter->isAjax()) {
                    $this->presenter->redirect('this');
                } else {
                    $form->setValues(array(), TRUE);
                    $this->redrawControl('userPasswordChangeForm');
                    $this->redrawControl('flashMessages');
                }
            };
            $form->onError[] = function($form) {
                $this->redrawControl('userPasswordChangeForm');
            };
        }
        return $form;
    }

    public function renderShow() {
        $this->template->userPasswordChangeFormEnabled = $this->userPasswordChangeFormEnabled;

        if ($this->drawAllowed) {
            //$this->drawPublications(true);
        }
    }

    public function actionShowAll($sort, $order, $keywords) {
        $this->drawAllowed = true;

        $this->template->userInfo = false;
        $this->template->userAdded = false;
        $this->template->userEdited = false;
        $this->template->userDeleted = false;
        $this->template->userRelated = array();
    }

    public function publicationAddNewUserSettingsFormError($form) {
        $this->redrawControl('publicationAddNewUserSettingsForm');
    }

    protected function createComponentPublicationEditUserSettingsForm($name) {
        $form = new \PublicationAddNewUserSettingsForm($this, $name);

        if(!$this->isCU()) unset($form['deadline_notification_advance']);

        $form->onError[] = function($form) {
            $this->redrawControl('publicationEditUserSettingsForm');
        };
        $form->onSuccess[] = function($form) {

            $formValues = $form->getValues();

            $this->drawAllowed = false;

            $this->userSettingsModel->update($formValues);

            $this->template->userSettingsEdited = true;

            $userSettings = $this->userSettingsModel->find($form->values->id);
            $this->template->userSettings = $userSettings;

            $this->flashMessage('Operation has been completed successfully.', 'alert-success');

            if (!$this->presenter->isAjax()) {
                $this->presenter->redirect('this');
            } else {
                $form->setValues(array(), TRUE);
                $this->redrawControl('publicationEditUserSettingsForm');
                $this->redrawControl('userShowSettings');
                $this->redrawControl('flashMessages');
            }
        };
        return $form;
    }

    public function handleEditUserSettings($userSettingsId) {

        $this->drawAllowed = false;

        $userSettings = $this->userSettingsModel->find($userSettingsId);

        $this["publicationEditUserSettingsForm"]->setDefaults($userSettings); // set up new values

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl('publicationEditUserSettingsForm');
        }
    }

    public function handleShowUserRelated($userId) {
        $this->drawAllowed = false;

        $this->template->userRelated = $this->context->Publication->findAllBy(array("submitter_id" => $userId));

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl('userRelated');
        }
    }


    // new

    /** @var  \App\Factories\IUserCrudFactory */
    protected $userCrudFactory;

    public function createComponentCrud(){
        $c = $this->userCrudFactory->create();

        $c->onAdd[] = function($row){
            $this->successFlashMessage("User has been added successfully");
            $this->redrawControl('userShowAll');
        };
        $c->onDelete[] = function($row) {
            $this->successFlashMessage("User has been deleted successfully");
            $this->redrawControl('userShowAll');
        };
        $c->onEdit[] = function($row) {
            $this->successFlashMessage("User has been edited successfully");
            $this->redrawControl('userShowAllRecords');
        };
        $c->onMessage[] = function($msg) {
            $this->successFlashMessage($msg);
        };

        return $c;
    }

    /**
     * @param \App\Factories\IUserCrudFactory $userCrudFactory
     */
    public function injectUserCrudFactory(\App\Factories\IUserCrudFactory $userCrudFactory) {
        $this->userCrudFactory = $userCrudFactory;
    }

    public function renderShowAll($keywords) {
        if(!$this->template->records) {    // can be loaded only single one in case of edit
            if ($keywords !== null) {
                $this["searchForm"]->setDefaults(array('keywords' => $keywords));
                $this->records = $this->submitterModel->findAllByKw($keywords);
            } else {
                $this->records = $this->submitterModel->findAll();
            }

            $sorting = $this["sorting"];
            /** @var $sorting \NasExt\Controls\SortingControl */

            $alphabetFilter = $this["alphabetFilter"];
            /** @var $alphabetFilter \App\Components\AlphabetFilter\AlphabetFilterComponent */

            if($alphabetFilter->getFilter()) $this->records->where('(surname LIKE ? OR surname LIKE ? OR nickname LIKE ? OR nickname LIKE ?)', $alphabetFilter->getFilter() . '%', strtolower($alphabetFilter->getFilter()) . "%", $alphabetFilter->getFilter() . '%', strtolower($alphabetFilter->getFilter()) . "%");

            $this->records->order($sorting->getColumn() . ' ' . $sorting->getSortDirection());

            $this->setupRecordsPaginator();

            $this->template->userAuthTypes =
                $this->baseAuthenticator->getUsersAuthenticationMethods($this->records);

            $this->template->records = $this->records;
        }
    }

    /**
     * @return \NasExt\Controls\SortingControl
     */
    protected function createComponentSorting()
    {
        $control = $this->sortingControlFactory->create( array(
            'surname' => 'surname',
            'name' => 'name',
            'nickname' => 'nickname',
            'email' => 'email'
        ),  'name', \NasExt\Controls\SortingControl::ASC);

        return $control;
    }

    public function createComponentAlphabetFilter($name) {
        $c = new \App\Components\AlphabetFilter\AlphabetFilterComponent($this, $name);
        $c->setAjaxRequest(true)->onFilter[] = function($filter) use ($name) {
            if ($this->isAjax()) $this->redrawControl('userShowAll');
        };
        return $c;
    }

    protected function createComponentAnnotationCrud(){
        $c = $this->annotationCrudFactory->create(0);
        $c->disallowAction('add');

        $cbFn = function(){
            $this->successFlashMessage('Operation has been completed successfully.');
            $this->redrawControl('publicationAnnotationData');
        };

        $c->onDelete[] = $cbFn;
        $c->onEdit[] = $cbFn;

        return $c;
    }

}
