<?php

namespace App\Presenters;

use App\Components\Publication\PublicationControl;
use App\Components\PublicationCategoryList\PublicationCategoryListComponent;
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

    /** @var \HomepageSearchForm @inject */
    public $HomepageSearchForm;

    /** @var  Model\Categories @inject */
    public $categoriesModel;

    /** @var  Model\Publication @inject */
    public $publicationModel;

    /** @var  \App\Factories\IPublicationCategoryListFactory @inject */
    public $publicationCategoryListFactory;

    /** @var \Nette\Http\Request @inject */
    public $httpRequest;


    /**
     *
     */
    public function actionDefault(): void
    {
        $this->template->categoriesTree = $this->categoriesModel->findAll()->order('name ASC');
        $dataAutocomplete = $this->publicationModel->getAuthorsNamesAndPubsTitles();
        $this->template->dataAutocomplete = json_encode($dataAutocomplete);
    }

    /**
     *
     */
    public function actionAbout(): void
    {
        //wtf???
    }

    /**
     * @return \Nette\Application\UI\Form
     */
    protected function createComponentHomepageSearchForm(): Form
    {
        $form = $this->HomepageSearchForm->create($this->data);

        $form->onSuccess[] = function ( $form) {
            $values = (array) $form->values;

            //serialize pubtypes from checkbox list
            if ($form->values->pubtype) {
                $types = '';

                foreach ($form->values->pubtype as $type) {
                    $types .= (' ' . $type);
                }

                $types = substr($types, 1);
                unset($values['pubtype']);
                $values['ptype'] = $types;
            }

            //serialize tags from checkbox list
            if ($form->values->tags) {
                $tags = '';

                foreach ($form->values->tags as $tag) {
                    $tags .= (' ' . $tag);
                }

                $tags = substr($tags, 1);
                $values['tags'] = $tags;
            } else {
                unset($values['tags']);
            }

            $this->presenter->redirect('Homepage:searchresults', $values);
        };

        return $form;
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
     * Handles search: retrieves results from model and initializes paginator.
     * @param array $params
     * @return array
     */
    public function search(array $params): array
    {
        $count = $this->publicationModel->searchCount($params);

        $vp = new \VisualPaginator();
        $this->addComponent($vp, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = $this->itemsPerPageDB;
        $paginator->itemCount = $count;

        $results = $this->publicationModel->search($params, $paginator->itemsPerPage, $paginator->offset);

        $publicationIds = [];
        foreach ($results as $result) {
            $publicationIds[] = $result->id;
        }
        $authorsByPubId = $this->authorModel->getAuthorsByMultiplePubIds($publicationIds);

        return [
            'resultsCount' => $count,
            'showingFrom' => $paginator->offset + 1,
            'showingTo' => ($paginator->offset + $paginator->itemsPerPage > $count) ? $count : ($paginator->offset + $paginator->itemsPerPage),
            'results' => $results,
            'authorsByPubId' => $authorsByPubId
        ];
    }

    /**
     * @param string $keywords
     * @param $ptype
     * @param $categories
     * @param $catop
     * @param $stype
     * @param $scope
     * @param string $sort
     */
    public function actionSearchResults($keywords, $ptype, $categories, $catop, $stype, $tags, $scope, $sort): void
    {
        $params = $this->httpRequest->getQuery();

        $pubtype = $ptype ? explode(' ', $ptype) : null;
        $tags = $tags ? explode(' ', $tags) : null;

        $searchParams = [
            'keywords' => $keywords,
            'categories' => $categories,
            'catOp' => $catop,
            'stype' => $stype,
            'tags' => $tags,
            'pubtype' => $pubtype,
            'scope' => $scope,
            'sort' => $sort
        ];

        $results = $this->search($searchParams);

        $this->template->results = $results['results'];
        $this->template->authorsByPubId = $results['authorsByPubId'];
        $this->template->resultsCount = $results['resultsCount'];
        $this->template->showingFrom = $results['showingFrom'];
        $this->template->showingTo = $results['showingTo'];

        $this->template->sort = $sort;
        $this->template->stype = $stype;
        unset($params['sort']);
        $this->template->params = $params;

        $this->data = $params;

        $this->template->categoriesTree = $this->categoriesModel->findAll()->order('name ASC');

        if (!$categories) {
            $categories = [];
        }
        $this->template->selectedCategories = $categories;
        $dataAutocomplete = $this->publicationModel->getAuthorsNamesAndPubsTitles();
        $this->template->dataAutocomplete = json_encode($dataAutocomplete);
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

    // new
    /**
     * @return \App\Components\PublicationCategoryList\PublicationCategoryListComponent
     */
    protected function createComponentPublicationCategoryList(): PublicationCategoryListComponent
    {
        $c = $this->publicationCategoryListFactory->create();

        $c->setHasControls(false);
        $c->setHasThreeStates(true);
        $c->setIsSelectable(true);

        return $c;
    }

    /**
     * @return PublicationControl
     */
    protected function createComponentPublication(): PublicationControl
    {
        return new PublicationControl();
    }

}
