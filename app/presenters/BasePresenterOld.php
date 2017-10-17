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
    private $numberOfUnconfirmed;

    public $dirPath;
    public $itemsPerPageDB;

    public $id; // historical meaning

    protected $userSettings;

    /** @var Model\Database */
    private $database;

    /**
     * @inject
     * @var Model\Publication
     */
    private $publication;


    protected function startup() {
        parent::startup();

        if ($this->getUser()->isLoggedIn()) {
            $this->template->numberOfUnconfirmed = $this->context->Publication->findAllBy(array('confirmed' => 0))->count();
            $userSettings = $this->context->UserSettings->findOneBy(array('submitter_id' => $this->user->id));
            $this->itemsPerPageDB = $userSettings->pagination;

            $this->userSettings = $userSettings;
        }

        $this->dirPath = $this->context->Files->dirPath;
        $this->template->dirPath = $this->dirPath;
        $this->template->dirPathTemplate = "/storage/";

        $this->template->presenterName = $this->name;
        $this->template->actionName = $this->action;
    }

    protected function createComponentSearchForm($name) {
        $form = new \SearchForm($this, $name);
        $form->onSuccess[] = $this->searchFormSucceeded;
        return $form;
    }

    public function searchFormSucceeded($form) {
        $this->presenter->redirect('this', (array) $form->getValues());
    }
}
