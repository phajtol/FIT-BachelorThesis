<?php

namespace App\Presenters;

use App\Components\Publication\PublicationControl;
use Nette;
use App\Model;


class AdminPresenter extends SecuredPresenter {

    /** @var Model\Reference @inject */
    public $referenceModel;

    /** @var Model\Publication @inject */
    public $publicationModel;

    /** @var Model\Author @inject */
    public $authorModel;

    /** @var Model\GeneralSettings @inject */
    public $generalSettingsModel;


    /**
     * @throws Nette\Application\AbortException
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
     * @param int $reference_id
     * @param int $publication_id
     * @throws Nette\Application\AbortException
     */
    public function handleConfirmReference(int $reference_id, int $publication_id): void
    {
        $this->referenceModel->confirm($publication_id, $reference_id);
        $this->redirect("this");
    }

    /**
     * @param $sort
     * @param $order
     * @param $keywords
     */
    public function actionShowUnconfirmed($sort, $order, $keywords): void
    {
        $this->drawAllowed = false;
        $this->template->publicationDeleted = false;
        $this->drawPublicationUnconfirmed();
    }

    /**
     *
     */
    public function renderReference(): void
    {
      $this->template->references = $this->referenceModel->findUnconfirmedWithPublication();
      $authorsByPubId = [];

      foreach ($this->template->references as $rec) {
          /** @var $rec Nette\Database\Table\ActiveRow */
          foreach ($rec['publication2']->related('author_has_publication')->order('priority ASC') as $authHasPub) {
              $author = $authHasPub->ref('author');
              if (!isset($authorsByPubId[$rec['publication2']->id])) {
                  $authorsByPubId[$rec['publication2']->id] = [];
              }
              $authorsByPubId[$rec['publication2']->id][] = $author;
          }
      }

      $this->template->authorsByPubId = $authorsByPubId;
    }

    /**
     * @param string $name
     * @return \AdminShowUnconfirmedForm
     */
    protected function createComponentAdminShowUnconfirmedForm(string $name): \AdminShowUnconfirmedForm
    {
        $form = new \AdminShowUnconfirmedForm($this, $name);

        $form->onSuccess[] = function (\AdminShowUnconfirmedForm $form) {
            $formValues = $form->getValues();

            foreach ($formValues as $key => $value) {
                $name = explode('_', $key);
                if ($value == 'TRUE') {
                    $this->publicationModel->update(['id' => $name[1], 'confirmed' => 1]);
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

    /**
     *
     */
    public function actionSettings(): void
    {
        $this->template->generalSettingsEdited = false;
        $this->template->generalSettings = $this->generalSettingsModel->findOneBy(['id' => 1]);
    }

    /**
     * @param string $name
     * @return \PublicationAddNewGeneralSettingsForm
     */
    protected function createComponentPublicationEditGeneralSettingsForm(string $name): \PublicationAddNewGeneralSettingsForm
    {
        $form = new \PublicationAddNewGeneralSettingsForm($this, $name);

        $form->onSuccess[] = function (\PublicationAddNewGeneralSettingsForm $form): void {
            $formValues = $form->getValues();
            $this->drawAllowed = false;

            $this->generalSettingsModel->update($formValues);

            $this->template->generalSettingsEdited = true;

            $generalSettings = $this->generalSettingsModel->find($form->values->id);
            $this->template->generalSettings = $generalSettings;

            $this->flashMessage('Operation has been completed successfully.', 'alert-success');

            if (!$this->presenter->isAjax()) {
                $this->presenter->redirect('this');
            } else {
                $form->setValues([], TRUE);
                $this->redrawControl('publicationEditGeneralSettingsForm');
                $this->redrawControl('settingsShowSettings');
                $this->redrawControl('flashMessages');
            }
        };

        $form->onError[] = function ($form): void {
            $this->redrawControl('publicationEditGeneralSettingsForm');
        };

        return $form;
    }

    /**
     * @throws Nette\Application\AbortException
     */
    public function handleEditGeneralSettings(): void
    {
        $this->drawAllowed = false;
        $generalSettings = $this->generalSettingsModel->findOneBy(['id' => 1]);

        $this['publicationEditGeneralSettingsForm']->setDefaults($generalSettings); // set up new values

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl('publicationEditGeneralSettingsForm');
        }
    }

    /**
     *
     */
    public function drawPublicationUnconfirmed(): void
    {
        $params = $this->getHttpRequest()->getQuery();

        if (!isset($params['sort'])) {
            $params['sort'] = 'title';
        }

        if (!isset($params['order'])) {
            $params['order'] = 'ASC';
        }

        if (!isset($this->template->records)) {
            if (isset($params['keywords'])) {
                $this->records = $this->publicationModel->findAllUnconfirmedByKw($params);
            } else {
                $this->records = $this->publicationModel->findAllUnconfirmed($params);
            }

            $this->setupRecordsPaginator();

            $authorsByPubId = [];

            foreach ($this->records as $record) {
                $authorsByPubId[$record->id] = $this->authorModel->getAuthorsNamesByPubIdPure($record->id);
            }

            $this->template->records = $this->records;
            $this->template->authorsByPubId = $authorsByPubId;
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
     * @param int $id
     * @param int $reference_id
     * @throws Nette\Application\AbortException
     */
    public function handleConfirm(int $id, int $reference_id): void
    {
        $this->referenceModel->confirm($id, $reference_id);

        if ($this->isAjax()) {
            $this->redrawControl('references');
        } else {
            $this->flashMessage('Reference confirmed.');
            $this->redirect("this");
        }
    }

    /**
     * @param int $id
     * @throws Nette\Application\AbortException
     */
    public function handleRefuse(int $id): void
    {
        $this->referenceModel->refuse($id);

        if ($this->isAjax()) {
            $this->redrawControl('references');
        } else {
            $this->flashMessage('Reference refused.');
            $this->redirect('this');
        }
    }

    /**
     * @throws Nette\Application\AbortException
     */
    public function handleProcess(): void
    {
        $count = $this->referenceModel->process();
        $this->flashMessage($count . ' reference processed.');
        $this->redirect('this');
    }


    /**
     * @return PublicationControl
     */
    public function createComponentPublication(): PublicationControl
    {
        return new PublicationControl();
    }

}
