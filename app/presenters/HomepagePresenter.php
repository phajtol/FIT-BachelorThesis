<?php

namespace App\Presenters;

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

        $form->onSuccess[] = function (Form $form) {
            $this->presenter->redirect('Homepage:searchresults', (array) $form->getValues());
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

    public function display_search_results_document($keywords, $categories, $operator, $searchtype, $starredpubs, $advanced, $sort): array
    {
        if ($advanced && $categories) {
            if ($operator == 'OR') {
                $itemCount = $this->publicationModel->getAllPubs_FullText_OR($keywords, $categories, $sort);
            } else {
                $itemCount = $this->publicationModel->getAllPubs_FullText_AND($keywords, $categories, $sort);
            }
        } else {
            if ($advanced) {
                $itemCount = $this->publicationModel->getAllPubs_FullText_advanced($keywords, $sort);
            } else {
                $itemCount = $this->publicationModel->getAllPubs_FullText($keywords, $sort);
            }
        }

        $vp = new \VisualPaginator();
        $this->addComponent($vp, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = $this->itemsPerPageDB;
        $paginator->itemCount = $itemCount['length'];

        if ($advanced && $categories) {
            if ($operator == 'OR') {
                $preResults = $this->publicationModel->getAllPubs_FullText_OR($keywords, $categories, $sort, $paginator->itemsPerPage, $paginator->offset);
            } else {
                $preResults = $this->publicationModel->getAllPubs_FullText_AND($keywords, $categories, $sort, $paginator->itemsPerPage, $paginator->offset);
            }
        } else {
            if ($advanced) {
                $preResults = $this->publicationModel->getAllPubs_FullText_advanced($keywords, $sort, $paginator->itemsPerPage, $paginator->offset);
            } else {
                $preResults = $this->publicationModel->getAllPubs_FullText($keywords, $sort, $paginator->itemsPerPage, $paginator->offset);
            }
        }

        $search_results = [];

        foreach ($preResults as $row) {
            $id = $row['id'];
            $title = $row['title'];
            $fulltext = stripslashes($row['content']);
            $pub_type = $row['pub_type'];
            $issue_year = $row['issue_year'];
            $issue_month = $row['issue_month'];

            $keywords_arr = explode(" ", str_replace('\"', '', $keywords));
            $title_higligthed = $this->highlight_str(htmlspecialchars($title), $keywords_arr);
            $passages_highlighted = '';

            foreach ($keywords_arr as $keyword) {
                if (strlen(trim($keyword)) != 0) {
                    $pos = stripos($fulltext, $keyword);
                    if ($pos === false) {
                        continue;
                    } else {
                        $space_to_end = strlen($fulltext) - $pos;
                        $length = $space_to_end >= 50 ? 100 : $space_to_end;
                        if ($pos < 50) {
                            $start = 0;
                            $passage = substr($fulltext, $start, $length);
                        } else {
                            $start = $pos - 50;
                            $passage = substr($fulltext, $start, $length);
                        }
                        $passage = htmlspecialchars($passage);
                        $i = -1;
                        while (isset($passage[++$i]) && $passage[$i] != ' ') {

                        }
                        $l = $j = strlen($passage);
                        while (isset($passage[--$j]) && $passage[$j] != ' ') {

                        }
                        $passage = substr($passage, $i, $j - $i);
                        $keyword_san = htmlspecialchars(preg_quote($keyword));
                        // $passage_higligthed = preg_replace("((.$keyword_san.)|(.$keyword_san$)|(^$keyword_san.))", " <strong>$keyword_san</strong> ", $passage);
                        $passage_higligthed = $this->highlight_str(htmlspecialchars($passage), array($keyword));
                        $passages_highlighted .= "$passage_higligthed... ";
                    }
                }
            }

            $annotationTag = $this->annotationModel->getAnnotationTag($id, $this->user->id);
            $authors = $this->authorHasPublicationModel->findAllBy(array('publication_id' => $id))->order('priority ASC');
            $categories = $this->categoriesHasPublicationModel->findAllBy(array('publication_id' => $id));

            $search_results[] = [
                'id' => $id,
                'title' => $title_higligthed,
                'authors' => $authors,
                'passages' => $passages_highlighted,
                'pub_type' => $pub_type,
                'issue_year'  => $issue_year,
                'issue_month'  => $issue_month,
                'categories' => $categories,
                'annotation' => $annotationTag
            ];
        }

        return $search_results;
    }

    /**
     * @param $keywords
     * @param $categories
     * @param $operator
     * @param $searchtype
     * @param $staredpubs
     * @param $advanced
     * @param $sort
     * @return array
     */
    public function display_search_results_document_starred_publications($keywords, $categories, $operator, $searchtype, $staredpubs, $advanced, $sort): array
    {
        if ($advanced && $categories) {
            if ($operator == 'OR') {
                $itemCount = $this->publicationModel->getAllPubs_FullText_OR_starred_publication($keywords, $categories, $sort, $this->user->id);
            } else {
                $itemCount = $this->publicationModel->getAllPubs_FullText_AND_starred_publication($keywords, $categories, $sort, $this->user->id);
            }
        } else {
            if ($advanced) {
                $itemCount = $this->publicationModel->getAllPubs_FullText_advanced_starred_publication($keywords, $sort, $this->user->id);
            } else {
                $itemCount = $this->publicationModel->getAllPubs_FullText_starred_publication($keywords, $sort, $this->user->id);
            }
        }

        $this->vp = new \VisualPaginator($this, 'vp');
        $paginator = $this->vp->getPaginator();
        $paginator->itemsPerPage = $this->itemsPerPageDB;
        $paginator->itemCount = $itemCount['length'];

        if ($advanced && $categories) {
            if ($operator == 'OR') {
                $preResults = $this->publicationModel->getAllPubs_FullText_OR_starred_publication($keywords, $categories, $sort, $this->user->id, $paginator->itemsPerPage, $paginator->offset);
            } else {
                $preResults = $this->publicationModel->getAllPubs_FullText_AND_starred_publication($keywords, $categories, $sort, $this->user->id, $paginator->itemsPerPage, $paginator->offset);
            }
        } else {
            if ($advanced) {
                $preResults = $this->publicationModel->getAllPubs_FullText_advanced_starred_publication($keywords, $sort, $this->user->id, $paginator->itemsPerPage, $paginator->offset);
            } else {
                $preResults = $this->publicationModel->getAllPubs_FullText_starred_publication($keywords, $sort, $this->user->id, $paginator->itemsPerPage, $paginator->offset);
            }
        }

        $search_results = [];

        foreach ($preResults as $row) {
            $id = $row['id'];
            $title = $row['title'];
            $fulltext = stripslashes($row['content']);
            $pub_type = $row['pub_type'];
            $issue_year = $row['issue_year'];
            $issue_month = $row['issue_month'];
            $keywords_arr = explode(" ", str_replace('\"', '', $keywords));
            $title_higligthed = $this->highlight_str(htmlspecialchars($title), $keywords_arr);
            $passages_highlighted = '';

            foreach ($keywords_arr as $keyword) {
                if (strlen(trim($keyword)) != 0) {
                    $pos = stripos($fulltext, $keyword);
                    if ($pos === false) {
                        continue;
                    } else {
                        $space_to_end = strlen($fulltext) - $pos;
                        $length = $space_to_end >= 50 ? 100 : $space_to_end;
                        if ($pos < 50) {
                            $start = 0;
                            $passage = substr($fulltext, $start, $length);
                        } else {
                            $start = $pos - 50;
                            $passage = substr($fulltext, $start, $length);
                        }
                        $passage = htmlspecialchars($passage);
                        $i = -1;
                        while ($passage[++$i] != ' ') {

                        }
                        $l = $j = strlen($passage);
                        while ($passage[--$j] != ' ') {

                        }
                        $passage = substr($passage, $i, $j - $i);
                        $keyword_san = htmlspecialchars(preg_quote($keyword));
                        // $passage_higligthed = preg_replace("((.$keyword_san.)|(.$keyword_san$)|(^$keyword_san.))", " <strong>$keyword_san</strong> ", $passage);
                        $passage_higligthed = $this->highlight_str(htmlspecialchars($passage), array($keyword));
                        $passages_highlighted .= "$passage_higligthed... ";
                    }
                }
            }

            $annotationTag = $this->annotationModel->getAnnotationTag($id, $this->user->id);
            $authors = $this->authorHasPublicationModel->findAllBy(array('publication_id' => $id))->order('priority ASC');
            $categories = $this->categoriesHasPublicationModel->findAllBy(array('publication_id' => $id));

            $search_results[] = [
                'id' => $id,
                'title' => $title_higligthed,
                'authors' => $authors,
                'passages' => $passages_highlighted,
                'pub_type' => $pub_type,
                'issue_year'  =>  $issue_year,
                'issue_month'  =>  $issue_month,
                'categories' => $categories,
                'annotation' => $annotationTag
            ];
        }

        return $search_results;
    }

    /**
     * @param $keywords
     * @param $categories
     * @param $operator
     * @param $searchtype
     * @param $starredpubs
     * @param $advanced
     * @param $sort
     * @return array
     */
    public function display_search_results_author($keywords, $categories, $operator, $searchtype, $starredpubs, $advanced, $sort): array
    {
        $keywords = trim($keywords);
        $keywordsString = preg_replace('/\s{2,}/', ' ', $keywords); //remove additional spaces
        $keywords = explode(' ', $keywordsString);

        if ($advanced && $categories) {
            if ($operator == 'OR') {
                $itemCount = $this->publicationModel->getAllPubs_Authors_OR($keywords, $keywordsString, $categories, $sort);
            } else {
                $itemCount = $this->publicationModel->getAllPubs_Authors_AND($keywords, $keywordsString, $categories, $sort);
            }
        } else {
            if ($advanced) {
                $itemCount = $this->publicationModel->getAllPubs_Authors_advanced($keywords, $keywordsString, $sort);
            } else {
                $itemCount = $this->publicationModel->getAllPubs_Authors($keywords, $keywordsString, $sort);
            }
        }


        $vp = new \VisualPaginator();
        $this->addComponent($vp, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = $this->itemsPerPageDB;
        $paginator->itemCount = $itemCount['length'];

        if ($advanced && $categories) {
            if ($operator == 'OR') {
                $preResults = $this->publicationModel->getAllPubs_Authors_OR($keywords, $keywordsString, $categories, $sort, $paginator->itemsPerPage, $paginator->offset);
            } else {
                $preResults = $this->publicationModel->getAllPubs_Authors_AND($keywords, $keywordsString, $categories, $sort, $paginator->itemsPerPage, $paginator->offset);
            }
        } else {
            if ($advanced) {
                $preResults = $this->publicationModel->getAllPubs_Authors_advanced($keywords, $keywordsString, $sort, $paginator->itemsPerPage, $paginator->offset);
            } else {
                $preResults = $this->publicationModel->getAllPubs_Authors($keywords, $keywordsString, $sort, $paginator->itemsPerPage, $paginator->offset);
            }
        }

        $search_results = [];

        //display found publications
        foreach ($preResults as $row) {
            $id = htmlspecialchars($row['id']);
            $title = htmlspecialchars($row['title']);
            $issue_year = $row['issue_year'];
            $issue_month = $row['issue_month'];

            $authorsString = "";
            $authors = $this->authorHasPublicationModel->findAllBy(array('publication_id' => $id))->order('priority ASC');

            foreach ($authors as $author) {
                $authorsString .= $this->authorModel->formNames($author->author->surname, $author->author->middlename, $author->author->name);
            }

            $authorsString = $this->highlight_str($authorsString, $keywords);
            $categories = $this->categoriesHasPublicationModel->findAllBy(array('publication_id' => $id));
            $pub_type = $row['pub_type'];

            $annotationTag = $this->annotationModel->getAnnotationTag($id, $this->user->id);

            $search_results[] = [
                'id' => $id,
                'title' => $title,
                'authors' => $authorsString,
                'pub_type' => $pub_type,
                'issue_year'  =>  $issue_year,
                'issue_month'  =>  $issue_month,
                'categories' => $categories,
                'annotation' => $annotationTag
            ];
        }

        return $search_results;
    }

    /**
     * @param $keywords
     * @param $categories
     * @param $operator
     * @param $searchtype
     * @param $starredpubs
     * @param $advanced
     * @param $sort
     * @return array
     */
    public function display_search_results_author_starred_publications($keywords, $categories, $operator, $searchtype, $starredpubs, $advanced, $sort): array
    {
        $keywords = trim($keywords);
        $keywordsString = preg_replace('/\s{2,}/', ' ', $keywords); //remove additional spaces
        $keywords = explode(' ', $keywordsString);


        if ($advanced && $categories) {
            if ($operator == 'OR') {
                $itemCount = $this->publicationModel->getAllPubs_Authors_OR_starred_publication($keywords, $keywordsString, $categories, $sort, $this->user->id);
            } else {
                $itemCount = $this->publicationModel->getAllPubs_Authors_AND_starred_publication($keywords, $keywordsString, $categories, $sort, $this->user->id);
            }
        } else {
            if ($advanced) {
                $itemCount = $this->publicationModel->getAllPubs_Authors_advanced_starred_publication($keywords, $keywordsString, $sort, $this->user->id);
            } else {
                $itemCount = $this->publicationModel->getAllPubs_Authors_starred_publication($keywords, $keywordsString, $sort, $this->user->id);
            }
        }


        $this->vp = new \VisualPaginator($this, 'vp');
        $paginator = $this->vp->getPaginator();
        $paginator->itemsPerPage = $this->itemsPerPageDB;
        $paginator->itemCount = $itemCount['length'];

        if ($advanced && $categories) {
            if ($operator == 'OR') {
                $preResults = $this->publicationModel->getAllPubs_Authors_OR_starred_publication($keywords, $keywordsString, $categories, $sort, $this->user->id, $paginator->itemsPerPage, $paginator->offset);
            } else {
                $preResults = $this->publicationModel->getAllPubs_Authors_AND_starred_publication($keywords, $keywordsString, $categories, $sort, $this->user->id, $paginator->itemsPerPage, $paginator->offset);
            }
        } else {
            if ($advanced) {
                $preResults = $this->publicationModel->getAllPubs_Authors_advanced_starred_publication($keywords, $keywordsString, $sort, $this->user->id, $paginator->itemsPerPage, $paginator->offset);
            } else {
                $preResults = $this->publicationModel->getAllPubs_Authors_starred_publication($keywords, $keywordsString, $sort, $this->user->id, $paginator->itemsPerPage, $paginator->offset);
            }
        }

        $search_results = [];

        //display found publications
        foreach ($preResults as $row) {
            $id = htmlspecialchars($row['id']);
            $title = htmlspecialchars($row['title']);
            $issue_year = $row['issue_year'];
            $issue_month = $row['issue_month'];

            $authorsString = "";
            $authors = $this->authorHasPublicationModel->findAllBy(array('publication_id' => $id))->order('priority ASC');

            foreach ($authors as $author) {
                $authorsString .= $this->authorModel->formNames($author->author->surname, $author->author->middlename, $author->author->name);
            }

            $authorsString = $this->highlight_str($authorsString, $keywords);
            $categories = $this->categoriesHasPublicationModel->findAllBy(array('publication_id' => $id));
            $pub_type = $row['pub_type'];

            $annotationTag = $this->annotationModel->getAnnotationTag($id, $this->user->id);

            $search_results[] = [
                'id' => $id,
                'title' => $title,
                'authors' => $authorsString,
                'pub_type' => $pub_type,
                'issue_year'  =>  $issue_year,
                'issue_month'  =>  $issue_month,
                'categories' => $categories,
                'annotation' => $annotationTag
            ];
        }

        return $search_results;
    }

    /**
     * @param $keywords
     * @param $categories
     * @param $operator
     * @param $searchtype
     * @param $starredpubs
     * @param $advanced
     * @param $sort
     */
    public function actionSearchResults($keywords, $categories, $operator, $searchtype, $starredpubs, $advanced, $sort): void
    {
        $params = $this->httpRequest->getQuery();

        if ($categories) {
            $categories = explode(" ", $categories);
        }

        if ($keywords) {
            $keywords = $this->remove_diac($keywords);
        }

        if ($starredpubs) {
            if ($searchtype == "fulltext" && $keywords) {
                $search_results = $this->display_search_results_document_starred_publications($keywords, $categories, $operator, $searchtype, $starredpubs, $advanced, $sort);
            } elseif ($searchtype == "authors" && $keywords) {
                $search_results = $this->display_search_results_author_starred_publications($keywords, $categories, $operator, $searchtype, $starredpubs, $advanced, $sort);
                $this->setView('searchresultsauthors');
            } elseif ($categories && !$keywords) {
                $search_results = $this->display_search_results_categories_starred_publications($keywords, $categories, $operator, $searchtype, $starredpubs, $advanced, $sort);
                $this->setView('searchresultsauthors');
            } else {
                $search_results = $this->display_search_results_starred_publications($keywords, $categories, $operator, $searchtype, $starredpubs, $advanced, $sort);
                $this->setView('searchresultsauthors');
            }
        } else {
            if ($searchtype == "fulltext" && $keywords) {
                $search_results = $this->display_search_results_document($keywords, $categories, $operator, $searchtype, $starredpubs, $advanced, $sort);
            } elseif ($searchtype == "authors" && $keywords) {
                $search_results = $this->display_search_results_author($keywords, $categories, $operator, $searchtype, $starredpubs, $advanced, $sort);
                $this->setView('searchresultsauthors');
            } elseif ($categories && !$keywords) {
                $search_results = $this->display_search_results_categories($keywords, $categories, $operator, $searchtype, $starredpubs, $advanced, $sort);
                $this->setView('searchresultsauthors');
            } else {
                $search_results = $this->display_search_results($keywords, $categories, $operator, $searchtype, $starredpubs, $advanced, $sort);
                $this->setView('searchresultsauthors');
            }
        }

        bdump($search_results);

        $this->template->results = $search_results;
        $this->template->sort = $sort;
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
     * @param $keywords
     * @param $categories
     * @param $operator
     * @param $searchtype
     * @param $starredpubs
     * @param $advanced
     * @param $sort
     * @return array
     */
    private function display_search_results_categories($keywords, $categories, $operator, $searchtype, $starredpubs, $advanced, $sort): array
    {
        if ($operator == 'OR') {
            $itemCount = $this->publicationModel->getAllPubs_Categories_OR($categories, $sort);
        } else {
            $itemCount = $this->publicationModel->getAllPubs_Categories_AND($categories, $sort);
        }

        $vp = new \VisualPaginator();
        $this->addComponent($vp, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = $this->itemsPerPageDB;
        $paginator->itemCount = $itemCount['length'];

        if ($operator == 'OR') {
            $preResults = $this->publicationModel->getAllPubs_Categories_OR($categories, $sort, $paginator->itemsPerPage, $paginator->offset);
        } else {
            $preResults = $this->publicationModel->getAllPubs_Categories_AND($categories, $sort, $paginator->itemsPerPage, $paginator->offset);
        }

        $search_results = [];

        //display found publications
        foreach ($preResults as $row) {
            $id = htmlspecialchars($row['id']);
            $title = htmlspecialchars($row['title']);
            $issue_year = $row['issue_year'];
            $issue_month = $row['issue_month'];

            $authorsString = "";
            $authors = $this->authorHasPublicationModel->findAllBy(array('publication_id' => $id))->order('priority ASC');

            foreach ($authors as $author) {
                $authorsString .= $this->authorModel->formNames($author->author->surname, $author->author->middlename, $author->author->name);
            }
            $categories = $this->categoriesHasPublicationModel->findAllBy(array('publication_id' => $id));
            $pub_type = $row['pub_type'];

            $annotationTag = $this->annotationModel->getAnnotationTag($id, $this->user->id);

            $search_results[] = [
                'id' => $id,
                'title' => $title,
                'authors' => $authorsString,
                'pub_type' => $pub_type,
                'issue_year'  =>  $issue_year,
                'issue_month'  =>  $issue_month,
                'categories' => $categories,
                'annotation' => $annotationTag
            ];
        }

        return $search_results;
    }

    /**
     * @param $keywords
     * @param $categories
     * @param $operator
     * @param $searchtype
     * @param $starredpubs
     * @param $advanced
     * @param $sort
     * @return array
     */
    private function display_search_results($keywords, $categories, $operator, $searchtype, $starredpubs, $advanced, $sort): array
    {
        $itemCount = $this->publicationModel->getAllPubs_no_params($categories, $sort);

        $vp = new \VisualPaginator();
        $this->addComponent($vp, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = $this->itemsPerPageDB;
        $paginator->itemCount = $itemCount['length'];

        $preResults = $this->publicationModel->getAllPubs_no_params($categories, $sort, $paginator->itemsPerPage, $paginator->offset);

        $search_results = [];

        //display found publications
        foreach ($preResults as $row) {
            $id = htmlspecialchars($row['id']);
            $title = htmlspecialchars($row['title']);
            $issue_year = $row['issue_year'];
            $issue_month = $row['issue_month'];

            $authorsString = "";
            $authors = $this->authorHasPublicationModel->findAllBy(array('publication_id' => $id))->order('priority ASC');

            foreach ($authors as $author) {
                $authorsString .= $this->authorModel->formNames($author->author->surname, $author->author->middlename, $author->author->name);
            }

            $authorsString = $this->highlight_str($authorsString, $keywords);
            $categories = $this->categoriesHasPublicationModel->findAllBy(array('publication_id' => $id));
            $pub_type = $row['pub_type'];

            $annotationTag = $this->annotationModel->getAnnotationTag($id, $this->user->id);

            $search_results[] = [
                'id' => $id,
                'title' => $title,
                'authors' => $authorsString,
                'pub_type' => $pub_type,
                'issue_year'  =>  $issue_year,
                'issue_month'  =>  $issue_month,
                'categories' => $categories,
                'annotation' => $annotationTag
            ];
        }

        return $search_results;
    }

    /**
     * @param $keywords
     * @param $categories
     * @param $operator
     * @param $searchtype
     * @param $starredpubs
     * @param $advanced
     * @param $sort
     * @return array
     */
    private function display_search_results_categories_starred_publications($keywords, $categories, $operator, $searchtype, $starredpubs, $advanced, $sort): array
    {
        if ($operator == 'OR') {
            $itemCount = $this->publicationModel->getAllPubs_Categories_OR_starred_publication($categories, $sort, $this->user->id);
        } else {
            $itemCount = $this->publicationModel->getAllPubs_Categories_AND_starred_publication($categories, $sort, $this->user->id);
        }

        $this->vp = new \VisualPaginator($this, 'vp');
        $paginator = $this->vp->getPaginator();
        $paginator->itemsPerPage = $this->itemsPerPageDB;
        $paginator->itemCount = $itemCount['length'];

        if ($operator == 'OR') {
            $preResults = $this->publicationModel->getAllPubs_Categories_OR_starred_publication($categories, $sort, $this->user->id, $paginator->itemsPerPage, $paginator->offset);
        } else {
            $preResults = $this->publicationModel->getAllPubs_Categories_AND_starred_publication($categories, $sort, $this->user->id, $paginator->itemsPerPage, $paginator->offset);
        }

        $search_results = [];

        //display found publications
        foreach ($preResults as $row) {
            $id = htmlspecialchars($row['id']);
            $title = htmlspecialchars($row['title']);
            $issue_year = $row['issue_year'];
            $issue_month = $row['issue_month'];

            $authorsString = "";
            $authors = $this->authorHasPublicationModel->findAllBy(array('publication_id' => $id))->order('priority ASC');

            foreach ($authors as $author) {
                $authorsString .= $this->authorModel->formNames($author->author->surname, $author->author->middlename, $author->author->name);
            }
            $categories = $this->categoriesHasPublicationModel->findAllBy(array('publication_id' => $id));
            $pub_type = $row['pub_type'];

            $annotationTag = $this->annotationModel->getAnnotationTag($id, $this->user->id);


            $search_results[] = [
                'id' => $id,
                'title' => $title,
                'authors' => $authorsString,
                'pub_type' => $pub_type,
                'issue_year'  =>  $issue_year,
                'issue_month'  =>  $issue_month,
                'categories' => $categories,
                'annotation' => $annotationTag
            ];
        }

        return $search_results;
    }

    /**
     * @param $keywords
     * @param $categories
     * @param $operator
     * @param $searchtype
     * @param $starredpubs
     * @param $advanced
     * @param $sort
     * @return array
     */
    private function display_search_results_starred_publications($keywords, $categories, $operator, $searchtype, $starredpubs, $advanced, $sort): array
    {
        $itemCount = $this->publicationModel->getAllPubs_no_params_starred_publication($categories, $sort, $this->user->id);

        $this->vp = new \VisualPaginator($this, 'vp');
        $paginator = $this->vp->getPaginator();
        $paginator->itemsPerPage = $this->itemsPerPageDB;
        $paginator->itemCount = $itemCount['length'];

        $preResults = $this->publicationModel->getAllPubs_no_params_starred_publication($categories, $sort, $this->user->id, $paginator->itemsPerPage, $paginator->offset);

        $search_results = [];

        //display found publications
        foreach ($preResults as $row) {
            $id = htmlspecialchars($row['id']);
            $title = htmlspecialchars($row['title']);
            $issue_year = $row['issue_year'];
            $issue_month = $row['issue_month'];

            $authorsString = "";
            $authors = $this->authorHasPublicationModel->findAllBy(array('publication_id' => $id))->order('priority ASC');

            foreach ($authors as $author) {
                $authorsString .= $this->authorModel->formNames($author->author->surname, $author->author->middlename, $author->author->name);
            }
            $categories = $this->categoriesHasPublicationModel->findAllBy(array('publication_id' => $id));
            $pub_type = $row['pub_type'];

            $annotationTag = $this->annotationModel->getAnnotationTag($id, $this->user->id);

            $search_results[] = [
                'id' => $id,
                'title' => $title,
                'authors' => $authorsString,
                'pub_type' => $pub_type,
                'issue_year'  =>  $issue_year,
                'issue_month'  =>  $issue_month,
                'categories' => $categories,
                'annotation' => $annotationTag
            ];
        }

        return $search_results;
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

}
