<?php

namespace App\Presenters;

use Nette,
    App\Model,
    \VisualPaginator,
    Nette\Diagnostics\Debugger;

class AdminPresenter extends SecuredPresenter {

    /** @var Nette\Database\Context */
    private $database;

	/** @var  Model\Publication */
	protected $publicationModel;


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
        $form->onSuccess[] = function($form) {

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
        };
        return $form;
    }

    public function actionSettings() {
        $this->template->generalSettingsEdited = false;
        $this->template->generalSettings = $this->context->GeneralSettings->findOneBy(array('id' => 1));
    }

    protected function createComponentPublicationEditGeneralSettingsForm($name) {
        $form = new \PublicationAddNewGeneralSettingsForm($this, $name);
        $form->onSuccess[] = function($form) {

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
        };
        $form->onError[] = function($form) {
            $this->redrawControl('publicationEditGeneralSettingsForm');
        };
        return $form;
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

    public function drawPublicationUnconfirmed() {

        Debugger::fireLog('drawPublicationUnconfirmed');
        $params = $this->getHttpRequest()->getQuery();

        if (!isset($params['sort'])) {
            $params['sort'] = 'title';
        }

        if (!isset($params['order'])) {
            $params['order'] = 'ASC';
        }

        if (!isset($this->template->records)) {
            Debugger::fireLog('--bbbbb');
            if (isset($params['keywords'])) {
                $this->records = $this->publicationModel->findAllUnconfirmedByKw($params);
            } else {
                $this->records = $this->publicationModel->findAllUnconfirmed($params);
            }

            $this->setupRecordsPaginator();

            $this->template->records = $this->records;
            $this->data = $params;

            if (isset($params['sort'])) {
                $this->template->sort = $params['sort'];
            } else {
                $this->template->sort = null;
            }

            if (isset($params['order'])) {
                $this->template->order = $params['order'];
            } else {
                $this->template->order = null;
            }

            if (isset($params['keywords'])) {
                $keywords = $params['keywords'];
            }

            $params = array();

            if (isset($keywords)) {
                $params['keywords'] = $keywords;
            }

            $this->template->params = $params;
        }

        $this->redrawControl('publicationShowAll');
    }

    /**
     * @param Model\Publication $publicationModel
     */
    public function injectPublicationModel(Model\Publication $publicationModel) {
            $this->publicationModel = $publicationModel;
    }
}
