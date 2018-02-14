<?php

namespace App\Presenters;

use Nette,
    App\Model,
    \VisualPaginator,
    Nette\Diagnostics\Debugger;

class AdminPresenter extends SecuredPresenter {

    /** @var Model\Reference @inject */
    public $referenceModel;

    /** @var Model\Publication @inject */
    public $publicationModel;

    /** @var Model\GeneralSettings @inject */
    public $generalSettingsModel;


    protected function startup() {
        parent::startup();
    }

    public function actionDefault() {

    }

    public function renderDefault() {
    }

    public function handleConfirmReference($reference_id, $publication_id) {
        $this->referenceModel->confirm($publication_id, $reference_id);
        $this->redirect("this");
    }

    public function actionShowUnconfirmed($sort, $order, $keywords) {
        Debugger::fireLog('actionShowUnconfirmed');
        $this->drawAllowed = false;
        $this->template->publicationDeleted = false;
        $this->drawPublicationUnconfirmed();
    }

    public function actionReference() {
      $this->template->references = $this->referenceModel->findUnconfirmedWithPublication();


      $authorsByPubId = array();
      foreach($this->template->references as $rec) {
          /** @var $rec Nette\Database\Table\ActiveRow */
          foreach($rec['publication2']->related('author_has_publication')->order('priority ASC') as $authHasPub) {
              $author = $authHasPub->ref('author');
              if(!isset($authorsByPubId[$rec['publication2']->id])) $authorsByPubId[$rec['publication2']->id] = [];
              $authorsByPubId[$rec['publication2']->id][] = $author;
          }
      }
      $this->template->authorsByPubId = $authorsByPubId;
    }

    protected function createComponentAdminShowUnconfirmedForm($name) {
        $form = new \AdminShowUnconfirmedForm($this, $name);
        $form->onSuccess[] = function(\AdminShowUnconfirmedForm $form) {

            Debugger::fireLog('adminShowUnconfirmedFormSucceeded()');

            $formValues = $form->getValues();

            foreach ($formValues as $key => $value) {
                $name = explode('_', $key);
                if ($value == 'TRUE') {
                    $this->publicationModel->update(array('id' => $name[1], 'confirmed' => 1));
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
        $this->template->generalSettings = $this->generalSettingsModel->findOneBy(array('id' => 1));
    }

    protected function createComponentPublicationEditGeneralSettingsForm($name) {
        $form = new \PublicationAddNewGeneralSettingsForm($this, $name);
        $form->onSuccess[] = function(\PublicationAddNewGeneralSettingsForm $form) {

            Debugger::fireLog('publicationEditGeneralSettingsFormSucceeded');

            $formValues = $form->getValues();

            $this->drawAllowed = false;

            Debugger::fireLog($form->values->id);
            Debugger::fireLog($this->action);

            $this->generalSettingsModel->update($formValues);

            $this->template->generalSettingsEdited = true;

            $generalSettings = $this->generalSettingsModel->find($form->values->id);
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

        $generalSettings = $this->generalSettingsModel->findOneBy(array('id' => 1));

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

    public function handleConfirm($id, $reference_id) {
        $this->referenceModel->confirm($id, $reference_id);
        $this->flashMessage("Reference confirmed.");
        $this->redirect("this");
    }

    public function handleRefuse($id) {
        $this->referenceModel->refuse($id);
        $this->flashMessage("Reference refused.");
        $this->redirect("this");
    }
}
