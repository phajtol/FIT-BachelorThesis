<?php

namespace App\Presenters;

use App\Components\Publication\PublicationControl;
use App\Model;
use Nette\Application\UI\Form;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends SecuredPresenter {

    /** @var  Model\Annotation @inject */
    public $annotationModel;

    /** @var  Model\CategoriesHasPublication @inject */
    public $categoriesHasPublicationModel;

    /** @var  Model\AuthorHasPublication @inject */
    public $authorHasPublicationModel;

    /** @var  Model\Author @inject */
    public $authorModel;

    /** @var  Model\Categories @inject */
    public $categoriesModel;

    /** @var  Model\Publication @inject */
    public $publicationModel;

    /** @var Model\Conference @inject */
    public $conferenceModel;

    /** @var  \App\Factories\IPublicationCategoryListFactory @inject */
    public $publicationCategoryListFactory;

    /** @var \Nette\Http\Request @inject */
    public $httpRequest;

    /** @var \App\Forms\SimpleSearchForm @inject */
    public $simpleSearchForm;


    /**
     *
     */
    public function actionDefault(): void
    {
        $starredCount = $this->publicationModel->findStarredCountByUserId($this->user->id);

        $vp = new \VisualPaginator();
        $this->addComponent($vp, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = $this->itemsPerPageDB;
        $paginator->itemCount = $starredCount;

        $starredPubs = $this->publicationModel->findStarredByUserId($this->user->id, $paginator->itemsPerPage, $paginator->offset);
        $pubIds = [];

        foreach ($starredPubs as $starredPub) {
            $pubIds[] = $starredPub->id;
        }

        $this->template->authorsByPubId = $this->authorModel->getAuthorsByMultiplePubIds($pubIds);
        $this->template->starredPubs = $starredPubs;

        $this->template->upcomingConfs = $this->conferenceModel->getUpcomingConferences($this->user->id);

        /*$this->template->categoriesTree = $this->categoriesModel->findAll()->order('name ASC');
        $dataAutocomplete = $this->publicationModel->getAuthorsNamesAndPubsTitles();
        $this->template->dataAutocomplete = json_encode($dataAutocomplete);*/
    }

    /**
     *
     */
    public function actionAbout(): void
    {
        //wtf???
    }


    /**
     * @param string $text
     * @return mixed
     */
    public function remove_diac(string $text) {
        $search = explode(",", "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
        $replace = explode(",", "c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
        $vt = str_replace($search, $replace, $text);
        return $vt;
    }

    /**
     * @param $str
     * @param $words
     * @return mixed
     */
    public function highlight_str($str, $words)
    {
        if (empty($words)) {
            return $str;
        }

        foreach ($words as $search) {
            $occurrences = substr_count(strtolower($str), strtolower($search));
            $newstring = $str;
            $match = [];

            for ($i = 0; $i < $occurrences; $i++) {
                $match[$i] = stripos($str, $search, $i);
                $match[$i] = substr($str, $match[$i], strlen($search));
                $newstring = str_replace($match[$i], '[#]' . $match[$i] . '[@]', strip_tags($newstring));
            }

            $newstring = str_replace('[#]', '<strong>', $newstring);
            $newstring = str_replace('[@]', '</strong>', $newstring);
        }

        return $newstring;
    }


    /**
     * @return Form
     */
    protected function createComponentPublicationSimpleSearchForm(): Form
    {
        $form = $this->simpleSearchForm->create();

        $form->onSuccess[] = function (Form $form) {
            $title = $form->values->title;

            //in case there's only one publication matching, redirect to it
            $res = $this->publicationModel->simpleSearch($title);

            if ($res->count() === 1) {
                $res = $res->fetch();
                $this->presenter->redirect('Publication:showpub', $res->id);
            }

            $this->presenter->redirect('Publication:search', ['stype' => 'title', 'keywords' => $title]);
        };

        return $form;
    }

    /**
     * @return Form
     */
    protected function createComponentConferenceSimpleSearchForm(): Form
    {
        $form = $this->simpleSearchForm->create();

        $form->onSuccess[] = function (Form $form) {
            $name = $form->values->title;
            $this->presenter->redirect('Conference:showall', ['keywords' => $name]);
        };

        return $form;
    }


    /**
     * @return PublicationControl
     */
    protected function createComponentPublication(): PublicationControl
    {
        return new PublicationControl();
    }

}
