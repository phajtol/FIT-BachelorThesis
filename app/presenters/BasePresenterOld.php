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


    // ================================================================
    // ========================== F I L E S ===========================
    // ================================================================
    // wtf? not strong enough to study this mess again...

    protected function createComponentUploadFileForm($name) {
        $form = new \UploadFileForm($this, $name);
        $form->onSuccess[] = $this->uploadFileFormSucceeded;
        return $form;
    }

    public function uploadFileFormSucceeded($form) {

        Debugger::fireLog('uploadFileFormSucceeded');

        $formValues = $form->getValues();


        Debugger::fireLog($formValues['file']);

        if ($formValues['file']->isOk()) {
            $filename = $formValues['file']->getSanitizedName();
            $targetPath = WWW_DIR . "/../uploads";
            if ($formValues['folderId'] !== '') {
                $targetPath .= "/" . $formValues['folderId'];
            }
            // @TODO vyřešit kolize
            Debugger::fireLog($filename);
            Debugger::fireLog($targetPath);
            $formValues['file']->move("$targetPath/$filename");

            Debugger::fireLog("$targetPath/$filename");
        }




        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $form->setValues(array(), TRUE);
            $this->redrawControl('uploadFileForm');
        }
    }


}
