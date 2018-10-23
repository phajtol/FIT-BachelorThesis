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

    public $vp;

    public $records;
    public $drawAllowed;

    public $data = array();

    public $itemsPerPageDB;

    public $id; // historical meaning

    protected $userSettings;


    /**
     * @var Model\UserSettings @inject
     */
    public $userSettingsModel;

    /**
     * @var Model\Publication @inject
     */
    public $publicationModel;

    /**
     * @var Model\Reference @inject
     */
    public $referenceModel;

    protected function startup() {
        parent::startup();

        if ($this->getUser()->isLoggedIn()) {
            $this->userSettings = $this->userSettingsModel->findOneBy(array('submitter_id' => $this->user->id));
            $this->itemsPerPageDB = $this->userSettings->pagination;
        }

        $this->template->dirPathTemplate = "/storage/";

        $this->template->presenterName = $this->name;
        $this->template->actionName = $this->action;
    }

    protected function createComponentSearchForm($name) {
        $form = new \SearchForm($this, $name);
        $form->onSuccess[] = function($form) {
            $this->presenter->redirect('this', (array) $form->getValues());
        };
        return $form;
    }
}
