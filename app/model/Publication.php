<?php

namespace App\Model;


use Nette\ArrayHash;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\Strings;

class Publication extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'publication';

    /** @var Author */
    protected $authorModel;

    /** @var \App\Helpers\Functions */
    protected $functions;

    /** @var Files */
    protected $filesModel;

    /** @var \Nette\Security\User */
    protected $user;

    public function __construct(Files $filesModel, \Nette\Security\User $user, Author $authorModel, \Nette\Database\Context $db)
    {
        parent::__construct($db);
        $this->authorModel = $authorModel;
        $this->user = $user;
        $this->filesModel = $filesModel;
        $this->functions = new \App\Helpers\Functions();
    }

    /**
     * @param array $params
     * @return Selection
     */
    public function findAllByKw(array $params): Selection
    {
        $records = $this->database->table('publication');

        if (isset($params['keywords'])) {
            $records = $records->where("title LIKE ?", "%" . $params['keywords'] . "%");
        }
        if (isset($params['filter']) && $params['filter'] != 'none') {
            $records = $records->where("title LIKE ?", $params['filter'] . "%");
        }
        $records = $records->order($params['sort'] . ' ' . $params['order']);

        return $records;
    }

    /**
     * @param array $params
     * @return Selection
     */
    public function findAllUnconfirmedByKw(array $params): Selection
    {
        return $this->getTable()
            ->select('journal.name AS journal, 
                journal.id AS journal_id,
                publisher.name AS publisher, 
                conference_year.location AS location, 
                conference_year.name AS name,
                conference_year.id AS cy_id,
                type_of_report AS type, 
                publication.id, 
                pub_type, 
                title, 
                volume, 
                number, 
                pages, 
                issue_month AS month_eng, 
                issue_year AS year, 
                url, 
                note, 
                editor, 
                edition, 
                publication.address, 
                howpublished, 
                chapter, 
                booktitle, 
                school, 
                institution, 
                conference_year_id')
            ->where(['confirmed' => 0])
            ->where('title LIKE ?', '%' . $params['keywords'] . '%')
            ->order($params['sort'] . ' ' . $params['order']);
    }

    /**
     * @param array $params
     * @return Selection
     */
    public function findAllUnconfirmed(array $params): Selection
    {
        return $this->getTable()
            ->select('journal.name AS journal,
                journal.id AS journal_id, 
                publisher.name AS publisher, 
                conference_year.location AS location, 
                conference_year.name AS name,
                conference_year.id AS cy_id,
                type_of_report AS type, 
                publication.id, 
                pub_type, 
                title, 
                volume, 
                number, 
                pages, 
                issue_month AS month_eng, 
                issue_year AS year, 
                url, 
                note, 
                editor, 
                edition, 
                publication.address, 
                howpublished, 
                chapter, 
                booktitle, 
                school, 
                institution, 
                conference_year_id')
            ->where(['confirmed' => 0])
            ->order($params['sort'] . ' ' . $params['order']);
    }

    /**
     * @return int
     */
    public function countUnConfirmed(): int
    {
        return $this->database->table('publication')->where(['confirmed' => 0])->count('*');
    }

    /**
     * @param int $publicationId
     */
    public function deleteAssociatedRecords(int $publicationId): void
    {
        $annotation = $this->database->table('annotation')->where(['publication_id' => $publicationId]);
        $authorHasPublication = $this->database->table('author_has_publication')->where(['publication_id' => $publicationId]);
        $categoriesHasPublication = $this->database->table('categories_has_publication')->where(['publication_id' => $publicationId]);
        $submitterHasPublication = $this->database->table('submitter_has_publication')->where(['publication_id' => $publicationId]);
        $documents = $this->database->table('documents')->where(['publication_id' => $publicationId]);
        $attribStorage = $this->database->table('attrib_storage')->where(['publication_id' => $publicationId]);
        $groupHasPublication = $this->database->table('group_has_publication')->where(['publication_id' => $publicationId]);
        $publication = $this->database->table('publication')->get($publicationId);

        foreach ($annotation as $item) {
            $item->delete();
        }

        foreach ($authorHasPublication as $item) {
            $item->delete();
        }

        foreach ($categoriesHasPublication as $item) {
            $item->delete();
        }

        foreach ($documents as $item) {
            $item->delete();
        }

        foreach ($submitterHasPublication as $item) {
            $item->delete();
        }

        foreach ($attribStorage as $attrib) {
            $attrib->delete();
        }

        foreach ($groupHasPublication as $item) {
            $item->delete();
        }

        if ($publication) {
            $publication->delete();
        }
    }

    /**
     * This is used to obtain necessary data to show publication in IEEE format.
     * @param int $id
     * @return Selection
     */
    public function getPubInfo(int $id): Selection
    {
        $publication = $this->getTable()
            ->select('journal.name AS journal,
                journal.id AS journal_id, 
                publisher.name AS publisher, 
                conference_year.location AS location, 
                conference_year.name AS name,
                conference_year.id AS cy_id,
                type_of_report AS type, 
                publication.id, 
                pub_type, 
                title, 
                volume, 
                number, 
                pages, 
                issue_month AS month_eng, 
                issue_year AS year, 
                url, 
                note, 
                editor, 
                edition, 
                publication.address, 
                howpublished, 
                chapter, 
                booktitle, 
                school, 
                institution, 
                conference_year_id')
            ->where('publication.id', $id);

        return $publication;
    }


    /**
     * This is used to obtain necessary data to show publication in IEEE format.
     * @param array $params
     * @return Selection
     */
    public function getMultiplePubInfoByKeywords(array $params): Selection
    {
        return $this->findAllByKw($params)
            ->select('journal.name AS journal, 
                journal.id AS journal_id,
                publisher.name AS publisher, 
                conference_year.location AS location, 
                conference_year.name AS name,
                conference_year.id AS cy_id,
                type_of_report AS type, 
                publication.id, 
                pub_type, 
                title, 
                volume, 
                number, 
                pages, 
                issue_month AS month_eng, 
                issue_year AS year, 
                url, 
                note, 
                editor, 
                edition, 
                publication.address, 
                howpublished, 
                chapter, 
                booktitle, 
                school, 
                institution, 
                conference_year_id');
    }

    /**
     * This is used to obtain necessary data to show publication in IEEE format.
     * @param array $params
     * @return Selection
     */
    public function getMultiplePubInfoByParams(array $params): Selection
    {
        return $this->findAllBy($params)
            ->select('journal.name AS journal, 
                journal.id AS journal_id,
                publisher.name AS publisher, 
                conference_year.location AS location, 
                conference_year.name AS name,
                conference_year.id AS cy_id,
                type_of_report AS type, 
                publication.id, 
                pub_type, 
                title, 
                volume, 
                number, 
                pages, 
                issue_month AS month_eng, 
                issue_year AS year, 
                url, 
                note, 
                editor, 
                edition, 
                publication.address, 
                howpublished, 
                chapter, 
                booktitle, 
                school, 
                institution, 
                conference_year_id');
    }

    /**
     * This is used to obtain necessary data to show publication in IEEE format.
     * @param array $ids
     * @return Selection
     */
    public function getMultiplePubInfoByIds(array $ids): Selection
    {
        return $this->getTable()
            ->select('journal.name AS journal, 
                journal.id AS journal_id,
                publisher.name AS publisher, 
                conference_year.location AS location, 
                conference_year.name AS name,
                conference_year.id AS cy_id,
                type_of_report AS type, 
                publication.id, 
                pub_type, 
                title, 
                volume, 
                number, 
                pages, 
                issue_month AS month_eng, 
                issue_year AS year, 
                url, 
                note, 
                editor, 
                edition, 
                publication.address, 
                howpublished, 
                chapter, 
                booktitle, 
                school, 
                institution, 
                conference_year_id')
            ->where('publication.id IN ?', $ids);
    }

    /**
     * This is used to obtain necessary data to show publication in IEEE format.
     * @param array $years
     * @return Selection
     */
    public function getPublicationsByConferenceYears(array $years): Selection
    {
        return $this->getTable()
            ->select('journal.name AS journal,
                journal.id AS journal_id, 
                publisher.name AS publisher, 
                conference_year.location AS location, 
                conference_year.name AS name,
                conference_year.id AS cy_id,
                type_of_report AS type, 
                publication.id, 
                pub_type, 
                title, 
                volume, 
                number, 
                pages, 
                issue_month AS month_eng, 
                issue_year AS year, 
                url, 
                note, 
                editor, 
                edition, 
                publication.address, 
                howpublished, 
                chapter, 
                booktitle, 
                school, 
                institution, 
                conference_year_id')
            ->where('conference_year_id IN ?', $years);
    }


    /**
     * @param $publicationCopy
     * @return array
     */
    public function getAllPubInfo($publicationCopy): array
    {
        $userId = $this->user->getId();
        $isAdmin = $this->user->isInRole('admin');
        $publication = $publicationCopy->toArray();
        $publication['location'] = '';

        $journal = $this->database->table('journal')->get($publicationCopy->journal_id);
        unset($publication['journal_id']);

        if ($journal) {
            $publication['journal'] = $journal->name;
        }

        $categories = $this->database->table('categories_has_publication')->where(['publication_id' => $publicationCopy->id]);
        $group = $this->database->table('group_has_publication')->where(['publication_id' => $publicationCopy->id]);
        $authors = $this->authorModel->getAuthorsNamesByPubId($publicationCopy->id);

        // bibtex
        $authorString = $this->authorModel->getAuthorsNamesByPubId($publicationCopy->id, ' and ');
        $publication['author'] = '';
        if ($authorString) {
            $publication['author'] = $authorString;
        }

        // endnote, refworks
        $authorArray = $this->authorModel->getAuthorsNamesByPubId($publicationCopy->id, null, 'endnote');
        $publication['author_array'] = [];
        if ($authorArray) {
            $publication['author_array'] = $authorArray;
        }

        $publisher = $this->database->table('publisher')->get($publicationCopy->publisher_id);
        unset($publication['publisher_id']);
        $publication['publisher'] = '';
        if ($publisher) {
            $publication['publisher'] = $publisher->name;
            $publication['publisher_address'] = $publisher->address;
        }

        if ($isAdmin) {
            $annotations = $this->database->table('annotation')->where('publication_id', $publicationCopy->id)->order("id ASC");
        } else {
            $annotations = $this->database->table('annotation')->where('publication_id', $publicationCopy->id)->where("submitter_id = ? OR global_scope = ?", $userId, 1)->order("id ASC");
        }

        $favourite = $this->database->table('submitter_has_publication')->where(array('submitter_id' => $userId, 'publication_id' => $publicationCopy->id))->fetch();
        $conferenceYearOriginal = $this->database->table('conference_year')->get($publicationCopy->conference_year_id);
        $conferenceYearPublisher = '';

        $publication['isbn'] = $publicationCopy->related('publication_isbn');

        if ($conferenceYearOriginal) {
            $conferenceYear = $conferenceYearOriginal->toArray();

            $publication['conference_year_w_year'] = $conferenceYear['w_year'] == '0000' ? '' : $conferenceYear['w_year'];
            $publication['location'] = $conferenceYear['location'];
            $publication['booktitle'] = $conferenceYear['name'];
            $publication['year'] = $conferenceYear['w_year'] == '0000' ? '' : $conferenceYear['w_year'];
            $publication['month'] = substr($conferenceYear['w_from'], 5, 2) == '00' ? '' : substr($conferenceYear['w_from'], 5, 2);

            $publication['from'] = '0000-00-00' ? '' : $conferenceYear['w_from'];
            $publication['from'] = '0000-00-00' ? '' : $conferenceYear['w_to'];
            $publication['month_eng'] = $this->functions->month_eng($publication['month']);
            $publication['month_cze'] = $this->functions->month_cze($publication['month']);

            $conferenceYearPublisher = $this->database->table('publisher')->get($conferenceYear['publisher_id']);
        } else {
            $publication['year'] = $publication['issue_year'];
            $publication['month'] = $publication['issue_month'];

            $publication['month_eng'] = $this->functions->month_eng($publication['month']);
            $publication['month_cze'] = $this->functions->month_cze($publication['month']);
        }


        if ($publication['pub_type'] == 'mastersthesis' || $publication['pub_type'] == 'phdthesis') {
            $publication['address'] = $publication['school']; // school_address???
        }

        if ($publication['pub_type'] == 'techreport') {
            $publication['type'] = $publication['type_of_report'];
        }

        if ($publication['pages']) {
            $pages = explode("-", $publication['pages']);
            $publication['pages_start'] = is_array($pages) && count($pages) == 2 ? preg_replace("/[^0-9]/", "", $pages[0]) : $publication['pages'];
            $publication['pages_end'] = is_array($pages) && count($pages) == 2 ? preg_replace("/[^0-9]/", "", $pages[1]) : $publication['pages'];
        }

        $attributes = $this->database->table('attrib_storage')->where(["publication_id" => $publicationCopy->id]);

        return [
            'attributes' => $attributes,
            'publication' => $publication,
            'journal' => $journal,
            'categories' => $categories,
            'group' => $group,
            'authors' => $authors,
            'publisher' => $publisher,
            'favourite' => $favourite,
            'annotations' => $annotations,
            'conferenceYear' => $conferenceYearOriginal,
            'conferenceYearPublisher' => $conferenceYearPublisher,
            'files' => $this->filesModel->prepareFiles($publicationCopy->id),
            'annotationAdded' => false,
            'annotationEdited' => false,
            'annotationDeleted' => false,
            'pubCit' => $publication,
            'pubCit_author_array' => $this->authorModel->getAuthorsNamesByPubIdPure($publicationCopy->id),
            'pubCit_author' => $this->authorModel->getAuthorsNamesByPubId($publicationCopy->id, ', ')
        ];
    }

    /**
     * @return ActiveRow
     */
    public function getLength(): ActiveRow
    {
        return $this->database->query('SELECT COUNT(publication.id) as length FROM publication')->fetch();
    }

    /**
     * @param int $submitterId
     * @param int $limit
     * @param int $offset
     * @param string $sort
     * @return ActiveRow
     */
    public function getAllPubs(int $submitterId, int $limit, int $offset, string $sort): ActiveRow
    {
        // spatne - zobrazuje to min vysledku, musim pres vnoreny select
        return $this->database->query('
          SELECT publication.*, submitter_has_publication.submitter_id as submitter_favouriter_id FROM publication
          LEFT JOIN submitter_has_publication ON publication.id = submitter_has_publication.publication_id
          WHERE submitter_has_publication.submitter_id = ?
          OR submitter_has_publication.submitter_id IS NULL
          ORDER BY publication.title ' . $sort . '
          LIMIT ? OFFSET ?
          ', $submitterId, $limit, $offset)->fetchAll();
    }

    //==================================

    /**
     * @param array $params - same content as searchResults method
     * @return int
     */
    public function searchCount(array $params): int
    {
        return $this->search($params, null, null)->count();
    }

    /**
     * @param array $params - content: keywords, categories, catOp, pubtype, stype, starredpubs, advanced, sort, ...
     * @param int $limit
     * @param int $offset
     * @return Selection
     */
    public function search(array $params, ?int $limit, ?int $offset): Selection
    {
        if ($params['stype'] === 'annotations') {
            $result = $this->database->table('annotation')
                ->select('publication.journal.name AS journal,
                    publication.journal.id AS journal_id,
                    publication.publisher.name AS publisher,
                    publication.conference_year.location AS location, 
                    publication.conference_year.name AS name,
                    publication.conference_year.id AS cy_id,
                    publication.type_of_report AS type, 
                    publication.id, 
                    publication.pub_type, 
                    publication.title, 
                    publication.volume, 
                    publication.number, 
                    publication.pages, 
                    publication.issue_month AS month_eng, 
                    publication.issue_year AS year, 
                    publication.url, 
                    publication.note, 
                    publication.editor, 
                    publication.edition, 
                    publication.address, 
                    publication.howpublished, 
                    publication.chapter, 
                    publication.booktitle, 
                    publication.school,
                    publication.institution, 
                    publication.conference_year_id,
                    submitter.name AS submitter_name,
                    submitter.surname AS submitter_surname,
                    annotation.text,
                    annotation.global_scope,
                    annotation.date,
                    annotation.id AS annotation_id')
                ->whereOr([
                    'global_scope = ?' => '1',
                    'annotation.submitter_id = ?' => $params['user_id']
                ]);
        } elseif ($params['stype'] === 'fulltext') {
            $result = $this->database->table('documents')
                ->select('publication.journal.name AS journal,
                    publication.journal.id AS journal_id,
                    publication.publisher.name AS publisher,
                    publication.conference_year.location AS location, 
                    publication.conference_year.name AS name,
                    publication.conference_year.id AS cy_id,
                    publication.type_of_report AS type, 
                    publication.id, 
                    publication.pub_type, 
                    publication.title, 
                    publication.volume, 
                    publication.number, 
                    publication.pages, 
                    publication.issue_month AS month_eng, 
                    publication.issue_year AS year, 
                    publication.url, 
                    publication.note, 
                    publication.editor, 
                    publication.edition, 
                    publication.address, 
                    publication.howpublished, 
                    publication.chapter, 
                    publication.booktitle, 
                    publication.school,
                    publication.institution, 
                    publication.conference_year_id');
        } else {
            $result = $this->database->table('publication')
                ->select('journal.name AS journal,
                    journal.id AS journal_id,
                    publisher.name AS publisher,
                    conference_year.location AS location, 
                    conference_year.name AS name,
                    conference_year.id AS cy_id,
                    type_of_report AS type, 
                    publication.id, 
                    pub_type, 
                    title, 
                    volume, 
                    number, 
                    pages, 
                    issue_month AS month_eng, 
                    issue_year AS year, 
                    url, 
                    note, 
                    editor, 
                    edition, 
                    publication.address, 
                    howpublished, 
                    chapter, 
                    booktitle, 
                    school,
                    institution, 
                    conference_year_id');
        }

        //pubtype
        if ($params['pubtype']) {
            $result = $result->where('publication.pub_type IN', $params['pubtype']);
        }

        //author
        if ($params['author']) {
            $authorsExpl = explode(',', $params['author']);
            $authors = [];

            foreach ($authorsExpl as $author) {
                $author = Strings::trim($author);
                $authors[] = explode(' ', $author);
            }

            $authorsPubs = $this->database->table('author_has_publication')
                ->select('publication_id');

            $masterCondition = '';
            $queryParams = [];

            foreach ($authors as $author) {
                $condition = '';

                foreach ($author as $authorsNames) {
                    if ($condition !== '') {
                        if (count($authors) > 1) {
                            $condition .= ' OR ';
                        } else {
                            $condition .= ' AND ';
                        }
                    }

                    $condition .= '(author.name LIKE ? OR author.surname LIKE ?)';

                    if (substr($authorsNames, -1) === '.') {
                        $queryParams[] = substr($authorsNames, 0, strlen($authorsNames) - 1) . '%';
                        $queryParams[] = substr($authorsNames, 0, strlen($authorsNames) - 1) . '%';
                    } else {
                        $queryParams[] = $authorsNames;
                        $queryParams[] = $authorsNames;
                    }
                }

                if ($masterCondition !== '') {
                    $masterCondition .= ' AND ';
                }

                $masterCondition .= $condition;
            }

            $authorsPubs = $authorsPubs->where($masterCondition, $queryParams)
                ->group('publication_id')
                ->having('COUNT(publication_id) = ?', count($authors))
                ->fetchPairs(null, 'publication_id');

            $result = $result->where('publication.id IN ?', $authorsPubs);
        }

        //keywords
        if ($params['keywords']) {
            if ($params['stype'] === 'annotations') {
                $result = $result->where('MATCH(text) AGAINST (? IN BOOLEAN MODE)', $params['keywords']);
            } elseif ($params['stype'] === 'fulltext') {
                $result = $result->whereOr([
                   'MATCH(content) AGAINST (? IN BOOLEAN MODE)' => $params['keywords'],
                   'MATCH(documents.title) AGAINST (? IN BOOLEAN MODE)' => $params['keywords']
                ]);
            } else {
                $keywords = explode(' ', $params['keywords']);
                $keywords2 = [];

                foreach ($keywords as $keyword) {
                    $keywords2[] = self::stripTitleForSearch($keyword);
                }

                $keywords = $keywords2;

                if (count($keywords) > 1) {
                    $condition = '';
                    $parameters = [];

                    foreach ($keywords as $keyword) {
                        if ($condition !== '') {
                            $condition .= ' OR ';
                        }
                        $condition .= 'publication.title_search LIKE ?';
                        $parameters[] = '%' . $keyword . '%';
                    }

                    $result = $result->where($condition, $parameters);
                } else {
                $result = $result->where('publication.title_search LIKE', '%' . $keywords[0] . '%');
                }
            }
        }

        //scope
        if ($params['scope']) {
            if ($params['scope'] === 'starred') {
                $starred = $this->database->table('submitter_has_publication')
                    ->select('publication_id')
                    ->where('submitter_id', $this->user->id)
                    ->fetchPairs(null, 'publication_id');

                $result = $result->where('publication.id IN ?', $starred);
            } else if ($params['scope'] === 'my') {
                $my = $this->database->table('author_has_publication')
                    ->select('publication_id')
                    ->where('author.user_id', $this->user->id)
                    ->fetchPairs(null, 'publication_id');

                $result = $result->where('publication.id IN ?', $my);
            } else if ($params['scope'] === 'annotated') {
                $annotated = $this->database->table('annotation')
                    ->select('publication_id')
                    ->where('submitter_id', $this->user->id)
                    ->fetchPairs(null, 'publication_id');

                $result = $result->where('publication.id IN ?', $annotated);
            }
        }

        //categories
        if ($params['categories']) {
            $categoryIds = explode(' ', $params['categories']);

            if ($params['catOp'] === 'or') {
                $categories = $this->database->table('categories_has_publication')
                    ->select('publication_id')
                    ->where('categories_id IN', $categoryIds)
                    ->fetchPairs(null, 'publication_id');
            } else {
                $categories = $this->database->table('categories_has_publication')
                    ->select('publication_id')
                    ->where('categories_id IN', $categoryIds)
                    ->group('publication_id')
                    ->having('COUNT(publication_id) = ?', count($categoryIds))
                    ->fetchPairs(null, 'publication_id');
            }

            $result = $result->where('publication.id IN', $categories);
        }

        //tags
        if ($params['tags']) {
            $tagged = $this->database->table('publication_has_tag')
                ->select('publication_id')
                ->where('tag_id IN', $params['tags'])
                ->fetchPairs(null, 'publication_id');

            $result = $result->where('publication.id IN ?', $tagged);
        }

        //sort
        if ($params['sort']) {
            if ($params['sort'] === 'title') {
                $result = $result->order('publication.title ASC');
            } else if ($params['sort'] === 'date') {
                $result = $result->order('publication.issue_year DESC, publication.issue_month DESC, publication.title ASC');
            }
        } else {
            if ($params['stype'] === 'annotations') {
                $result = $result->order('MATCH(text) AGAINST (?) DESC', $params['keywords']);
            } elseif ($params['stype'] === 'fulltext') {
                $result = $result->order('5 * MATCH(documents.title) AGAINST (?) + MATCH(content) AGAINST (?) DESC', $params['keywords'], $params['keywords']);
            } else {
                $result = $result->order('publication.title ASC');
            }
        }

        //limit + offset
        if ($limit !== null && $offset !== null) {
            $result = $result->limit($limit, $offset);
        }

        return $result;
    }

    /**
     * @param string $title
     * @return Selection
     */
    public function simpleSearch(string $title): Selection
    {
        return $this->getTable()
            ->select('id')
            ->where('title_search LIKE ?', '%' . $title . '%');
    }


    public function arr_condition($arr, $column) {
        $condition = '(';
        $count = count($arr) - 1;
        foreach ($arr as $v) {
            $condition .= "$column = '$v'" . ($count-- ? " OR " : '');
        }
        return $condition . ')';
    }

    public function prepareFormData($formValues) {

        /*
          title => "ewrwe" (5)
          abstract => "rerer" (5)
          pub_type => "article" (7)
          categories => ""
          authors => ""
          volume => ""
          number => ""
          chapter => ""
          pages => ""
          editor => ""
          edition => ""
          address => ""
          booktitle => ""
          school => ""
          institution => ""
          type_of_report => ""
          publisher_id => NULL
          journal_id => NULL
          conference2 => NULL
          conference_year_id => NULL
          howpublished => ""
          organization => ""
          url => ""
          note => ""
          upload => array ()
         */

        switch ($formValues['pub_type']) {
            case "misc":
                $formValues = $this->setMisc($formValues);
                break;
            case "book":
                $formValues = $this->setBook($formValues);
                break;
            case "article":
                $formValues = $this->setArticle($formValues);
                break;
            case "inproceedings":
                $formValues = $this->setInproceedings($formValues);
                break;
            case "proceedings":
                $formValues = $this->seProceedings($formValues);
                break;
            case "incollection":
                $formValues = $this->setIncollection($formValues);
                break;
            case "inbook":
                $formValues = $this->setInbook($formValues);
                break;
            case "booklet":
                $formValues = $this->setBooklet($formValues);
                break;
            case "manual":
                $formValues = $this->setManual($formValues);
                break;
            case "techreport":
                $formValues = $this->setTechreport($formValues);
                break;
            case "mastersthesis":
                $formValues = $this->setMastersthesis($formValues);
                break;
            case "phdthesis":
                $formValues = $this->setPhdthesis($formValues);
                break;
            case "unpublished":
                $formValues = $this->setUnpublished($formValues);
                break;
        }

        return $formValues;
    }

    /*
      $formValues['volume'] = NULL;
      $formValues['number'] = NULL;
      $formValues['chapter'] = NULL;
      $formValues['pages'] = NULL;
      $formValues['editor'] = NULL;
      $formValues['edition'] = NULL;
      $formValues['address'] = NULL;
      $formValues['booktitle'] = NULL;
      $formValues['school'] = NULL;
      $formValues['institution'] = NULL;
      $formValues['type_of_report'] = NULL;
      $formValues['publisher_id'] = NULL;
      $formValues['journal_id'] = NULL;
      $formValues['conference_year_id'] = NULL;
      $formValues['howpublished'] = NULL;
      $formValues['organization'] = NULL;
     */

    /**
     * @param \Nette\Utils\ArrayHash $formValues
     * @return \Nette\Utils\ArrayHash
     */
    public function setMisc(\Nette\Utils\ArrayHash $formValues): \Nette\Utils\ArrayHash {
        $formValues['volume'] = NULL;
        $formValues['number'] = NULL;
        $formValues['chapter'] = NULL;
        $formValues['editor'] = NULL;
        $formValues['edition'] = NULL;
        $formValues['address'] = NULL;
        $formValues['booktitle'] = NULL;
        $formValues['school'] = NULL;
        $formValues['institution'] = NULL;
        $formValues['type_of_report'] = NULL;
        $formValues['publisher_id'] = NULL;
        $formValues['journal_id'] = NULL;
        $formValues['conference_year_id'] = NULL;
        $formValues['organization'] = NULL;

        return $formValues;
    }

    /**
     * @param \Nette\Utils\ArrayHash $formValues
     * @return \Nette\Utils\ArrayHash
     */
    public function setBook(\Nette\Utils\ArrayHash $formValues): \Nette\Utils\ArrayHash
    {
        $formValues['chapter'] = NULL;
        $formValues['address'] = NULL;
        $formValues['booktitle'] = NULL;
        $formValues['school'] = NULL;
        $formValues['institution'] = NULL;
        $formValues['type_of_report'] = NULL;
        $formValues['journal_id'] = NULL;
        $formValues['conference_year_id'] = NULL;
        $formValues['howpublished'] = NULL;
        $formValues['organization'] = NULL;

        return $formValues;
    }

    /**
     * @param \Nette\Utils\ArrayHash $formValues
     * @return \Nette\Utils\ArrayHash
     */
    public function setArticle(\Nette\Utils\ArrayHash $formValues): \Nette\Utils\ArrayHash
    {
        $formValues['chapter'] = NULL;
        $formValues['editor'] = NULL;
        $formValues['edition'] = NULL;
        $formValues['address'] = NULL;
        $formValues['booktitle'] = NULL;
        $formValues['school'] = NULL;
        $formValues['institution'] = NULL;
        $formValues['type_of_report'] = NULL;
        $formValues['publisher_id'] = NULL;
        $formValues['conference_year_id'] = NULL;
        $formValues['howpublished'] = NULL;
        $formValues['organization'] = NULL;

        return $formValues;
    }

    /**
     * @param \Nette\Utils\ArrayHash $formValues
     * @return \Nette\Utils\ArrayHash
     */
    public function setInproceedings(\Nette\Utils\ArrayHash $formValues): \Nette\Utils\ArrayHash
    {
        $formValues['editor'] = NULL;
        $formValues['edition'] = NULL;
        $formValues['address'] = NULL;
        $formValues['booktitle'] = NULL;
        $formValues['school'] = NULL;
        $formValues['institution'] = NULL;
        $formValues['type_of_report'] = NULL;
        $formValues['publisher_id'] = NULL;
        $formValues['journal_id'] = NULL;
        $formValues['howpublished'] = NULL;

        return $formValues;
    }

    /**
     * @param \Nette\Utils\ArrayHash $formValues
     * @return \Nette\Utils\ArrayHash
     */
    public function seProceedings(\Nette\Utils\ArrayHash $formValues): \Nette\Utils\ArrayHash
    {
        $formValues['chapter'] = NULL;
        $formValues['editor'] = NULL;
        $formValues['edition'] = NULL;
        $formValues['address'] = NULL;
        $formValues['booktitle'] = NULL;
        $formValues['school'] = NULL;
        $formValues['institution'] = NULL;
        $formValues['type_of_report'] = NULL;
        $formValues['publisher_id'] = NULL;
        $formValues['journal_id'] = NULL;
        $formValues['howpublished'] = NULL;

        return $formValues;
    }

    /**
     * @param \Nette\Utils\ArrayHash $formValues
     * @return \Nette\Utils\ArrayHash
     */
    public function setIncollection(\Nette\Utils\ArrayHash $formValues): \Nette\Utils\ArrayHash
    {
        $formValues['editor'] = NULL;
        $formValues['address'] = NULL;
        $formValues['school'] = NULL;
        $formValues['institution'] = NULL;
        $formValues['type_of_report'] = NULL;
        $formValues['journal_id'] = NULL;
        $formValues['conference_year_id'] = NULL;
        $formValues['howpublished'] = NULL;
        $formValues['organization'] = NULL;

        return $formValues;
    }

    /**
     * @param \Nette\Utils\ArrayHash $formValues
     * @return \Nette\Utils\ArrayHash
     */
    public function setInbook(\Nette\Utils\ArrayHash $formValues): \Nette\Utils\ArrayHash
    {
        $formValues['address'] = NULL;
        $formValues['booktitle'] = NULL;
        $formValues['school'] = NULL;
        $formValues['institution'] = NULL;
        $formValues['type_of_report'] = NULL;
        $formValues['journal_id'] = NULL;
        $formValues['conference_year_id'] = NULL;
        $formValues['howpublished'] = NULL;
        $formValues['organization'] = NULL;

        return $formValues;
    }

    /**
     * @param \Nette\Utils\ArrayHash $formValues
     * @return \Nette\Utils\ArrayHash
     */
    public function setBooklet(\Nette\Utils\ArrayHash $formValues): \Nette\Utils\ArrayHash
    {
        $formValues['volume'] = NULL;
        $formValues['number'] = NULL;
        $formValues['chapter'] = NULL;
        $formValues['editor'] = NULL;
        $formValues['edition'] = NULL;
        $formValues['booktitle'] = NULL;
        $formValues['school'] = NULL;
        $formValues['institution'] = NULL;
        $formValues['type_of_report'] = NULL;
        $formValues['publisher_id'] = NULL;
        $formValues['journal_id'] = NULL;
        $formValues['conference_year_id'] = NULL;
        $formValues['organization'] = NULL;

        return $formValues;
    }

    /**
     * @param \Nette\Utils\ArrayHash $formValues
     * @return \Nette\Utils\ArrayHash
     */
    public function setManual(\Nette\Utils\ArrayHash $formValues): \Nette\Utils\ArrayHash
    {
        $formValues['volume'] = NULL;
        $formValues['number'] = NULL;
        $formValues['chapter'] = NULL;
        $formValues['editor'] = NULL;
        $formValues['booktitle'] = NULL;
        $formValues['school'] = NULL;
        $formValues['institution'] = NULL;
        $formValues['type_of_report'] = NULL;
        $formValues['publisher_id'] = NULL;
        $formValues['journal_id'] = NULL;
        $formValues['conference_year_id'] = NULL;
        $formValues['howpublished'] = NULL;

        return $formValues;
    }

    /**
     * @param \Nette\Utils\ArrayHash $formValues
     * @return \Nette\Utils\ArrayHash
     */
    public function setTechreport(\Nette\Utils\ArrayHash $formValues): \Nette\Utils\ArrayHash
    {
        $formValues['volume'] = NULL;
        $formValues['chapter'] = NULL;
        $formValues['editor'] = NULL;
        $formValues['edition'] = NULL;
        $formValues['booktitle'] = NULL;
        $formValues['school'] = NULL;
        $formValues['publisher_id'] = NULL;
        $formValues['journal_id'] = NULL;
        $formValues['conference_year_id'] = NULL;
        $formValues['howpublished'] = NULL;
        $formValues['organization'] = NULL;

        return $formValues;
    }

    /**
     * @param \Nette\Utils\ArrayHash $formValues
     * @return \Nette\Utils\ArrayHash
     */
    public function setMastersthesis(\Nette\Utils\ArrayHash $formValues): \Nette\Utils\ArrayHash
    {
        $formValues['volume'] = NULL;
        $formValues['number'] = NULL;
        $formValues['chapter'] = NULL;
        $formValues['editor'] = NULL;
        $formValues['edition'] = NULL;
        $formValues['booktitle'] = NULL;
        $formValues['institution'] = NULL;
        $formValues['type_of_report'] = NULL;
        $formValues['publisher_id'] = NULL;
        $formValues['journal_id'] = NULL;
        $formValues['conference_year_id'] = NULL;
        $formValues['howpublished'] = NULL;
        $formValues['organization'] = NULL;

        return $formValues;
    }

    /**
     * @param \Nette\Utils\ArrayHash $formValues
     * @return \Nette\Utils\ArrayHash
     */
    public function setPhdthesis(\Nette\Utils\ArrayHash $formValues): \Nette\Utils\ArrayHash
    {
        $formValues['volume'] = NULL;
        $formValues['number'] = NULL;
        $formValues['chapter'] = NULL;
        $formValues['editor'] = NULL;
        $formValues['edition'] = NULL;
        $formValues['booktitle'] = NULL;
        $formValues['institution'] = NULL;
        $formValues['type_of_report'] = NULL;
        $formValues['publisher_id'] = NULL;
        $formValues['journal_id'] = NULL;
        $formValues['conference_year_id'] = NULL;
        $formValues['howpublished'] = NULL;
        $formValues['organization'] = NULL;

        return $formValues;
    }

    /**
     * @param \Nette\Utils\ArrayHash $formValues
     * @return \Nette\Utils\ArrayHash
     */
    public function setUnpublished(\Nette\Utils\ArrayHash $formValues): \Nette\Utils\ArrayHash
    {
        $formValues['volume'] = NULL;
        $formValues['number'] = NULL;
        $formValues['chapter'] = NULL;
        $formValues['editor'] = NULL;
        $formValues['edition'] = NULL;
        $formValues['address'] = NULL;
        $formValues['booktitle'] = NULL;
        $formValues['school'] = NULL;
        $formValues['institution'] = NULL;
        $formValues['type_of_report'] = NULL;
        $formValues['publisher_id'] = NULL;
        $formValues['journal_id'] = NULL;
        $formValues['conference_year_id'] = NULL;
        $formValues['howpublished'] = NULL;
        $formValues['organization'] = NULL;

        return $formValues;
    }

    /**
     * @return array
     */
    public function getPairs(): array
    {
        $params = [];
        $arr = [];
        $params['sort'] = 'title';
        $params['order'] = 'ASC';
        $all = $this->findAllByKw($params);

        foreach ($all as $one) {
            $arr[$one->id] = $one->title;
        }

        return $arr;
    }

    /**
     * @param int $publication_id
     * @param int|null $text_reference_id
     * @return array
     */
    public function getPairsForReference(int $publication_id, ?int $text_reference_id = null): array
    {
        $arr = [];
        $idNot = $this->database->table('reference')
            ->select('reference_id')
            ->where('publication_id = ?', $publication_id)
            ->where('reference_id IS NOT NULL');

        $query = $this->database->table('publication')
            ->where('id != ?',$publication_id)
            ->where('id NOT', $idNot);

        $reference = $this->database->fetch('SELECT * FROM reference where id = ?;', $text_reference_id);

        if (empty($text_reference_id)) {
            $publications = $query->order("title");
        } else {
            $publications = $query->order('MATCH(title) AGAINST (? IN BOOLEAN MODE) DESC' , $reference->title);
        }

        foreach ($publications as $one) {
            $authors = $this->authorModel->getAuthorsNamesByPubId($one->id, '; ');
            $arr[$one->id] = $one->title . ' (' . $authors . '; id: ' . $one->id . ')';
        }

        return $arr;
    }

    /**
     * Gets publications where user with $id is author ready to be given to the PublicationControl component.
     * @param int $id
     * @return Selection
     */
    public function findAllByUserId(int $id): Selection
    {
        $pubs = $this->database->table('author_has_publication')
            ->select('publication_id')
            ->where('author.user_id', $id)
            ->fetchPairs(null, 'publication_id');

        return $this->database->table('publication')
            ->select('journal.name AS journal,
                    journal.id AS journal_id,
                    publisher.name AS publisher,
                    conference_year.location AS location, 
                    conference_year.name AS name,
                    conference_year.id AS cy_id,
                    type_of_report AS type, 
                    publication.id, 
                    pub_type, 
                    title, 
                    volume, 
                    number, 
                    pages, 
                    issue_month AS month_eng, 
                    issue_year AS year, 
                    url, 
                    note, 
                    editor, 
                    edition, 
                    publication.address, 
                    howpublished, 
                    chapter, 
                    booktitle, 
                    school,
                    institution, 
                    conference_year_id')
            ->where('publication.id IN ?', $pubs);
    }

    /**
     * Gets starred publications for user with $id ready to be given to the PublicationControl component.
     * @param int $id
     * @param int $limit
     * @param $offset
     * @return Selection
     */
    public function findStarredByUserId(int $id, int $limit, $offset): Selection
    {
        return $this->database->table('submitter_has_publication')
            ->select('publication.journal.name AS journal,
                    publication.journal.id AS journal_id,
                    publication.publisher.name AS publisher,
                    publication.conference_year.location AS location, 
                    publication.conference_year.name AS name,
                    publication.conference_year.id AS cy_id,
                    publication.type_of_report AS type, 
                    publication.id, 
                    publication.pub_type, 
                    publication.title, 
                    publication.volume, 
                    publication.number, 
                    publication.pages, 
                    publication.issue_month AS month_eng, 
                    publication.issue_year AS year, 
                    publication.url, 
                    publication.note, 
                    publication.editor, 
                    publication.edition, 
                    publication.address, 
                    publication.howpublished, 
                    publication.chapter, 
                    publication.booktitle, 
                    publication.school,
                    publication.institution, 
                    publication.conference_year_id')
            ->where('submitter_has_publication.submitter_id', $id)
            ->limit($limit, $offset);
    }

    /**
     * Gets starred publications for user with $id ready to be given to the PublicationControl component.
     * @param int $id
     * @return int
     */
    public function findStarredCountByUserId(int $id): int
    {
        return $this->database->table('submitter_has_publication')
            ->where('submitter_has_publication.submitter_id', $id)
            ->count();
    }

    /**
     * This is used to remove characters like multiple spaces, commas, dashes and so on from title for search.
     * @param string $title
     * @return string
     */
    static public function stripTitleForSearch(string $title): string
    {
        $title = str_replace(['.', '?', '!', ',', ':', ';', '\'', '`', '"', '<', '>', '-', '_', '|', '+', '*', '(', ')', '@', '#', '$', '€', '^', '~', '&', '[', ']', '{', '}', '%', '°'], ' ', $title);
        $title = Strings::toAscii($title);
        $title = Strings::replace($title, '/\s\s+/', ' ');
        return Strings::normalize($title);
    }
}
