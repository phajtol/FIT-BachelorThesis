<?php

namespace App\Presenters;

use Nette,
    App\Model,
    \VisualPaginator,
    Nette\Diagnostics\Debugger;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class UserPresenter extends SecuredPresenter {

    /** @var \App\Services\Authenticators\LoginPassAuthenticator @inject */
    public $loginPassAuthenticator;

    /** @var Model\Submitter @inject */
    public $submitterModel;

    /** @var Model\Annotation @inject */
    public $annotationModel;

    /** @var Model\UserSettings @inject */
    public $userSettingsModel;

    /** @var Model\Publication @inject */
    public $publicationModel;

    /** @var Model\Tag @inject */
    public $tagModel;

    /** @var \App\Services\Authenticators\BaseAuthenticator @inject */
    public $baseAuthenticator;

    /** @var \App\Factories\IAnnotationCrudFactory @inject */
    public $annotationCrudFactory;

    /** @var \App\Factories\ITagCrudFactory @inject */
    public $tagCrudFactory;

    /** @var bool */
    protected $userPasswordChangeFormEnabled = false;


    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup(): void
    {
        parent::startup();
    }

    /**
     *
     */
    public function actionDefault(): void
    {

    }

    /**
     *
     */
    public function renderDefault(): void
    {

    }

    /**
     * @param $sort
     * @param $order
     * @param $keywords
     * @throws Nette\Application\BadRequestException
     */
    public function actionShow($sort, $order, $keywords): void
    {

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

        $myPublications = $this->publicationModel->findAllByUserId($this->user->id);

        $myTags = $this->tagModel->findAllByUserId($this->user->id);

        $this->template->submitter = $submitter;
        $this->template->annotations = $annotations;
        $this->template->userSettings = $userSettings;
        $this->template->myPublications = $myPublications;
        $this->template->tags = $myTags;

        $this->template->annotationAdded = false;
        $this->template->annotationEdited = false;
        $this->template->annotationDeleted = false;

        $this->template->publicationDeleted = false;
    }

    /**
     * @param $name
     * @return \UserPasswordChangeForm
     */
    protected function createComponentUserPasswordChangeForm($name): \UserPasswordChangeForm
    {
        $user = $this->submitterModel->find($this->user->id);

        $form = new \UserPasswordChangeForm($this->loginPassAuthenticator, $user, $this, $name);

        if ($this->userPasswordChangeFormEnabled) {
            $form->onSuccess[] = function (\UserPasswordChangeForm $form) {

                $user = $this->submitterModel->find($this->user->id);
                $this->loginPassAuthenticator->associateLoginPasswordToUser($this->user->id, $user->nickname, $form['pass']->getValue());

                $this->flashMessage('Your Password has been changed successfully.', 'alert-success');
                $this->template->passwordChanged = true;

                if (!$this->presenter->isAjax()) {
                    $this->presenter->redirect('this');
                } else {
                    $form->clearValues();
                    $this->redrawControl('userPasswordChangeForm');
                    $this->redrawControl('flashMessages');
                }
            };

            $form->onError[] = function ($form) {
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
        $form->onSuccess[] = function(\PublicationAddNewUserSettingsForm $form) {

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

        $this->template->userRelated = $this->publicationModel->findAllBy(array("submitter_id" => $userId));

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl('userRelated');
        }
    }


    // new

    /** @var  \App\Factories\IUserCrudFactory  @inject */
    public $userCrudFactory;

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

    protected function createComponentTagCrud(){
        $c = $this->tagCrudFactory->create();

        $cbFn = function(){
            $this->successFlashMessage('Operation has been completed successfully.');
            $this->redrawControl('tags');
        };
        $c->onAdd[] = $cbFn;
        $c->onDelete[] = $cbFn;
        $c->onEdit[] = $cbFn;

        return $c;
    }

}
