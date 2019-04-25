<?php

namespace App\Presenters;

use Nette,
    App\Model,
    \VisualPaginator,
    Nette\Diagnostics\Debugger;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenterOld extends Nette\Application\UI\Presenter {

    /** @var  */
    public $vp;

    /** @var  */
    public $records;

    /** @var bool */
    public $drawAllowed;

    /** @var array  */
    public $data = [];

    /** @var int */
    public $itemsPerPageDB;

    public $id; // historical meaning

    /** @var  */
    protected $userSettings;

    /** @var Model\UserSettings @inject */
    public $userSettingsModel;

    /** @var Model\Publication @inject */
    public $publicationModel;

    /** @var Model\Reference @inject */
    public $referenceModel;

    /** @var Model\RightsRequest @inject */
    public $rightsRequestModel;

    /** @var Model\UserRole @inject */
    public $userRoleModel;


    /**
     *
     */
    protected function startup(): void
    {
        parent::startup();

        if ($this->getUser()->isLoggedIn()) {
            $roles = $this->userRoleModel->getAllByUserId($this->user->id);
            $this->user->getIdentity()->setRoles($roles);

            $this->userSettings = $this->userSettingsModel->findOneBy(['submitter_id' => $this->user->id]);
            $this->itemsPerPageDB = $this->userSettings->pagination;
        }

        if ($this->user->isInRole('admin')) {
            $this->template->unconfirmedCount = $this->publicationModel->countUnConfirmed();
            $this->template->rightsRequestCount = $this->rightsRequestModel->getWaitingCount();
        }

        $this->template->dirPathTemplate = '/storage/';

        $this->template->presenterName = $this->name;
        $this->template->actionName = $this->action;
    }

    /**
     * @param string $name
     * @return \SearchForm
     */
    protected function createComponentSearchForm(string $name): \SearchForm
    {
        $form = new \SearchForm($this, $name);

        $form->onSuccess[] = function ($form) {
            $this->presenter->redirect('this', (array) $form->getValues());
        };

        return $form;
    }
}
