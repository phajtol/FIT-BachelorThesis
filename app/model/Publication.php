<?php

namespace App\Model;


use Nette\ArrayHash;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

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
    public function findAllByKw(array $params): Selection {
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
        return $this->database->table('publication')
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
        return $this->database->table('publication')->where(['confirmed' => 0])->order($params['sort'] . ' ' . $params['order']);
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
        $references = $this->database->table('reference')->where(array('publication_id' => $publicationCopy->id))->order("id ASC");
        $citations = $this->database->table('reference')->where(array('reference_id' => $publicationCopy->id))->order("id ASC");


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
            'references' => $references,
            'citations' => $citations,
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

    /**
     * @param $keywords
     * @param $categories
     * @param $sort
     * @param null $limit
     * @param null $offset
     * @return ActiveRow
     */
    public function getAllPubs_FullText_OR($keywords, $categories, $sort, $limit = null, $offset = null): ActiveRow {
        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery($limit, $sort);
        $selectQuery = $this->getSelectQueryOR($limit);

        $query = $selectQuery . "
          FROM documents d
          JOIN publication p ON p.id = d.publication_id
          JOIN categories_has_publication c ON p.id = c.publication_id
          WHERE (MATCH(d.title) AGAINST (? IN BOOLEAN MODE) OR MATCH(d.content) AGAINST (? IN BOOLEAN MODE))
          AND c.categories_id IN (?)
          " . $orderQuery . $limitQuery;

        if ($limit) {
            if ($sort == "date" || $sort == "title") {
                $result = $this->database->query($query, $keywords, $keywords, $categories, $limit, $offset)->fetchAll();
            } else {
                $result = $this->database->query($query, $keywords, $keywords, $categories, $keywords, $keywords, $limit, $offset)->fetchAll();
            }
        } else {
            $result = $this->database->query($query, $keywords, $keywords, $categories)->fetch();
        }

        return $result;
    }

    /** @return Nette\Database\Table\ActiveRow */
    public function getAllPubs_FullText_OR_starred_publication($keywords, $categories, $sort, $userId, $limit = null, $offset = null) {
        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery($limit, $sort);
        $selectQuery = $this->getSelectQueryOR($limit);

        $query = $selectQuery . "
          FROM documents d
          JOIN publication p ON p.id = d.publication_id
          JOIN categories_has_publication c ON p.id = c.publication_id
          WHERE (MATCH(d.title) AGAINST (? IN BOOLEAN MODE) OR MATCH(d.content) AGAINST (? IN BOOLEAN MODE))
          AND c.categories_id IN (?)
          AND p.id IN (SELECT x.publication_id FROM submitter_has_publication x WHERE x.submitter_id = $userId)
          " . $orderQuery . $limitQuery;


        if ($limit) {
            if ($sort == "date" || $sort == "title") {
                $result = $this->database->query($query, $keywords, $keywords, $categories, $limit, $offset)->fetchAll();
            } else {
                $result = $this->database->query($query, $keywords, $keywords, $categories, $keywords, $keywords, $limit, $offset)->fetchAll();
            }
        } else {
            $result = $this->database->query($query, $keywords, $keywords, $categories)->fetch();
        }

        return $result;
    }

    /** @return Nette\Database\Table\ActiveRow */
    public function getAllPubs_FullText_AND($keywords, $categories, $sort, $limit = null, $offset = null) {

        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery($limit, $sort);
        $selectQuery = $this->getSelectQueryAND($limit);

        $query = $selectQuery . "
                  FROM documents d
		  JOIN publication p ON p.id = d.publication_id
		  WHERE (MATCH(d.title) AGAINST (? IN BOOLEAN MODE) OR MATCH(d.content) AGAINST (? IN BOOLEAN MODE))
		  AND p.id IN (
                    SELECT publication_id
                    FROM categories_has_publication
                    WHERE categories_id IN (?)
                    GROUP BY (publication_id)
                    HAVING COUNT(publication_id) = ?
		  )" . $orderQuery . $limitQuery;

        if ($limit) {
            if ($sort == "date" || $sort == "title") {
                $result = $this->database->query($query, $keywords, $keywords, $categories, count($categories), $limit, $offset)->fetchAll();
            } else {
                $result = $this->database->query($query, $keywords, $keywords, $categories, count($categories), $keywords, $keywords, $limit, $offset)->fetchAll();
            }
        } else {
            $result = $this->database->query($query, $keywords, $keywords, $categories, count($categories))->fetch();
        }

        return $result;
    }

    /** @return Nette\Database\Table\ActiveRow */
    public function getAllPubs_FullText_AND_starred_publication($keywords, $categories, $sort, $userId, $limit = null, $offset = null) {

        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery($limit, $sort);
        $selectQuery = $this->getSelectQueryAND($limit);

        // $where_clause = $this->arr_condition($categories, 'categories_id');

        $query = $selectQuery . "
                  FROM documents d
                  JOIN publication p ON p.id = d.publication_id
		  WHERE (MATCH(d.title) AGAINST (? IN BOOLEAN MODE) OR MATCH(d.content) AGAINST (? IN BOOLEAN MODE))
                  AND p.id IN (
		  SELECT publication_id
		  FROM categories_has_publication
		  WHERE categories_id IN (?)
		  GROUP BY (publication_id)
		  HAVING COUNT(publication_id) = ?
                    )
                  AND p.id IN (SELECT publication_id FROM submitter_has_publication WHERE submitter_id = $userId)" . $orderQuery . $limitQuery;

        if ($limit) {
            if ($sort == "date" || $sort == "title") {
                $result = $this->database->query($query, $keywords, $keywords, $categories, count($categories), $limit, $offset)->fetchAll();
            } else {
                $result = $this->database->query($query, $keywords, $keywords, $categories, count($categories), $keywords, $keywords, $limit, $offset)->fetchAll();
            }
        } else {
            $result = $this->database->query($query, $keywords, $keywords, $categories, count($categories))->fetch();
        }

        return $result;
    }

    /** @return Nette\Database\Table\ActiveRow */
    public function getAllPubs_FullText_advanced($keywords, $sort, $limit = null, $offset = null) {

        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery($limit, $sort);
        $selectQuery = $this->getSelectQueryAND($limit);

        $query = $selectQuery . "
                  FROM documents d
                  JOIN publication p ON p.id = d.publication_id
		  WHERE (MATCH(d.title) AGAINST (? IN BOOLEAN MODE) OR MATCH(d.content) AGAINST (? IN BOOLEAN MODE))
                  AND p.id NOT IN (
                                SELECT publication_id
                                FROM categories_has_publication
                                )" . $orderQuery . $limitQuery;

        if ($limit) {
            if ($sort == "date" || $sort == "title") {
                $result = $this->database->query($query, $keywords, $keywords, $limit, $offset)->fetchAll();
            } else {
                $result = $this->database->query($query, $keywords, $keywords, $keywords, $keywords, $limit, $offset)->fetchAll();
            }
        } else {
            $result = $this->database->query($query, $keywords, $keywords)->fetch();
        }

        return $result;
    }

    /** @return Nette\Database\Table\ActiveRow */
    public function getAllPubs_FullText_advanced_starred_publication($keywords, $sort, $userId, $limit = null, $offset = null) {

        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery($limit, $sort);
        $selectQuery = $this->getSelectQueryAND($limit);

        $query = $selectQuery . "
                FROM documents d JOIN publication p ON p.id = d.publication_id
		WHERE (MATCH(d.title) AGAINST (? IN BOOLEAN MODE) OR MATCH(d.content) AGAINST (? IN BOOLEAN MODE))
		AND p.id NOT IN (
		SELECT publication_id
		FROM categories_has_publication
		)
		AND p.id IN (SELECT publication_id FROM submitter_has_publication WHERE submitter_id = $userId)" . $orderQuery . $limitQuery;

        if ($limit) {
            if ($sort == "date" || $sort == "title") {
                $result = $this->database->query($query, $keywords, $keywords, $limit, $offset)->fetchAll();
            } else {
                $result = $this->database->query($query, $keywords, $keywords, $keywords, $keywords, $limit, $offset)->fetchAll();
            }
        } else {
            $result = $this->database->query($query, $keywords, $keywords)->fetch();
        }

        return $result;
    }

    /** @return Nette\Database\Table\ActiveRow */
    public function getAllPubs_FullText($keywords, $sort, $limit = null, $offset = null) {

        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery($limit, $sort);
        $selectQuery = $this->getSelectQueryAND($limit);

        $query = $selectQuery . "
                   FROM documents d
                   JOIN publication p ON p.id = d.publication_id
                   WHERE (MATCH(d.title) AGAINST (? IN BOOLEAN MODE) OR MATCH(d.content) AGAINST (? IN BOOLEAN MODE))
                  " . $orderQuery . $limitQuery;

        if ($limit) {
            if ($sort == "date" || $sort == "title") {
                $result = $this->database->query($query, $keywords, $keywords, $limit, $offset)->fetchAll();
            } else {
                $result = $this->database->query($query, $keywords, $keywords, $keywords, $keywords, $limit, $offset)->fetchAll();
            }
        } else {
            $result = $this->database->query($query, $keywords, $keywords)->fetch();
        }

        return $result;
    }

    /** @return Nette\Database\Table\ActiveRow */
    public function getAllPubs_FullText_starred_publication($keywords, $sort, $userId, $limit = null, $offset = null) {

        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery($limit, $sort);
        $selectQuery = $this->getSelectQueryAND($limit);

        $query = $selectQuery . "
                FROM documents d JOIN publication p ON p.id = d.publication_id
		WHERE ((MATCH(d.title) AGAINST (? IN BOOLEAN MODE) OR MATCH(d.content) AGAINST (? IN BOOLEAN MODE))
		AND p.id IN (SELECT publication_id FROM submitter_has_publication WHERE submitter_id = $userId))
                  " . $orderQuery . $limitQuery;

        if ($limit) {
            if ($sort == "date" || $sort == "title") {
                $result = $this->database->query($query, $keywords, $keywords, $limit, $offset)->fetchAll();
            } else {
                $result = $this->database->query($query, $keywords, $keywords, $keywords, $keywords, $limit, $offset)->fetchAll();
            }
        } else {
            $result = $this->database->query($query, $keywords, $keywords)->fetch();
        }

        return $result;
    }

    public function getLimitQuery($limit) {
        $limitQuery = "";
        if ($limit) {
            $limitQuery = " LIMIT ? OFFSET ?";
        }
        return $limitQuery;
    }

    public function getSelectQueryOR($limit) {
        $selectQuery = "";
        if ($limit) {
            $selectQuery = "SELECT DISTINCT p.id, p.title, d.content, p.pub_type, p.issue_year, p.issue_month ";
        } else {
            $selectQuery = "SELECT COUNT(DISTINCT p.id) AS length ";
        }
        return $selectQuery;
    }

    public function getSelectQueryAND($limit) {
        $selectQuery = "";
        if ($limit) {
            $selectQuery = "SELECT p.id, p.title, d.content, p.pub_type, p.issue_year, p.issue_month ";
        } else {
            $selectQuery = "SELECT COUNT(p.id) AS length ";
        }
        return $selectQuery;
    }

    public function getAuthorsNamesAndPubsTitles() {
        $authors = $this->database->table('author')->order("surname ASC, name ASC");
        $publications = $this->database->table('publication')->select('title')->order('title ASC');
        $dataTemp = array();

        foreach ($authors as $author) {
            $dataTemp[] = $author->surname . ' ' . ($author->middlename ? $author->middlename . ' ' : '') . ($author->name);
        }
        foreach ($publications as $publication) {
            $dataTemp[] = $publication->title;
        }

        return $dataTemp;
    }

    public function getOrderQuery($limit, $sort) {

        if (!$limit) {
            return "";
        }

        switch ($sort) {
            case "title":
                $order_clause = " ORDER BY p.title ASC ";
                break;
            case "date":
                $order_clause = " ORDER BY p.issue_year DESC, p.issue_month DESC, p.title ASC ";
                break;
            default:
                $order_clause = " ORDER BY 5 * MATCH(d.title) AGAINST (?) + MATCH(d.content) AGAINST (?) DESC ";
        }

        return $order_clause;
    }

    /** @return Nette\Database\Table\ActiveRow */
    public function getAllPubs_Authors_OR($keywords, $keywordsString, $categories, $sort, $limit = null, $offset = null) {
        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery_Author_OR($limit, $sort);
        $selectQuery = $this->getSelectQuery_Author_OR($limit);

        $conditions = '(';
        foreach ($keywords as $w) {
            if (strlen($w) < 3 && $w != "and")//jump over abbreviations
                continue;
            $conditions .= "a.name LIKE '%$w%' OR a.middlename LIKE '%$w%' OR a.surname LIKE '%$w%' OR ";
        }
        $conditions .= "p.title LIKE '%$keywordsString%' OR ";
        $conditions .= ' false)';


        $query = $selectQuery . "
            FROM publication p
            JOIN `categories_has_publication` c ON p.id = c.publication_id
            JOIN author_has_publication ap ON p.id = ap.publication_id
            JOIN author a ON a.id = ap.author_id
            WHERE $conditions
            AND c.categories_id IN (?)
            " . $orderQuery . $limitQuery;

        if ($limit) {
            $result = $this->database->query($query, $categories, $limit, $offset)->fetchAll();
        } else {
            $result = $this->database->query($query, $categories)->fetch();
        }

        return $result;
    }

    /** @return Nette\Database\Table\ActiveRow */
    public function getAllPubs_Authors_OR_starred_publication($keywords, $keywordsString, $categories, $sort, $userId, $limit = null, $offset = null) {
        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery_Author_OR($limit, $sort);
        $selectQuery = $this->getSelectQuery_Author_OR($limit);

        $conditions = '(';
        foreach ($keywords as $w) {
            if (strlen($w) < 3 && $w != "and")//jump over abbreviations
                continue;
            $conditions .= "a.name LIKE '%$w%' OR a.middlename LIKE '%$w%' OR a.surname LIKE '%$w%' OR ";
        }
        $conditions .= "p.title LIKE '%$keywordsString%' OR ";
        $conditions .= ' false)';

        $query = $selectQuery . "
                FROM publication p
                JOIN categories_has_publication c ON p.id = c.publication_id
                JOIN author_has_publication ap ON p.id = ap.publication_id
                JOIN author a ON a.id = ap.author_id
                WHERE $conditions
                AND c.categories_id IN (?)
                AND p.id IN (SELECT publication_id from submitter_has_publication WHERE submitter_id = $userId)
            " . $orderQuery . $limitQuery;

        if ($limit) {
            $result = $this->database->query($query, $categories, $limit, $offset)->fetchAll();
        } else {
            $result = $this->database->query($query, $categories)->fetch();
        }

        return $result;
    }

    public function getAllPubs_Authors_AND($keywords, $keywordsString, $categories, $sort, $limit = null, $offset = null) {
        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery_Author_OR($limit, $sort);
        $selectQuery = $this->getSelectQuery_Author_OR($limit);

        $conditions = '(';
        foreach ($keywords as $w) {
            if (strlen($w) < 3 && $w != "and")//jump over abbreviations
                continue;
            $conditions .= "a.name LIKE '%$w%' OR a.middlename LIKE '%$w%' OR a.surname LIKE '%$w%' OR ";
        }
        $conditions .= "p.title LIKE '%$keywordsString%' OR ";
        $conditions .= ' false)';

        $query = $selectQuery . "
            FROM publication p
            JOIN author_has_publication ap ON p.id = ap.publication_id
            JOIN author a ON a.id = ap.author_id
            WHERE $conditions
            AND p.id IN (
                    SELECT publication_id
                    FROM categories_has_publication
                    WHERE categories_id IN (?)
                    GROUP BY (publication_id)
                    HAVING COUNT(publication_id) = ?
            ) " . $orderQuery . $limitQuery;

        if ($limit) {
            $result = $this->database->query($query, $categories, count($categories), $limit, $offset)->fetchAll();
        } else {
            $result = $this->database->query($query, $categories, count($categories))->fetch();
        }

        return $result;
    }

    public function getAllPubs_Authors_AND_starred_publication($keywords, $keywordsString, $categories, $sort, $userId, $limit = null, $offset = null) {
        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery_Author_OR($limit, $sort);
        $selectQuery = $this->getSelectQuery_Author_OR($limit);

        $conditions = '(';
        foreach ($keywords as $w) {
            if (strlen($w) < 3 && $w != "and")//jump over abbreviations
                continue;
            $conditions .= "a.name LIKE '%$w%' OR a.middlename LIKE '%$w%' OR a.surname LIKE '%$w%' OR ";
        }
        $conditions .= "p.title LIKE '%$keywordsString%' OR ";
        $conditions .= ' false)';

        $query = $selectQuery . "
           FROM publication p
           JOIN author_has_publication ap ON p.id = ap.publication_id
           JOIN author a ON a.id = ap.author_id
           WHERE $conditions
           AND p.id IN (
                SELECT publication_id
                FROM categories_has_publication
                WHERE categories_id IN (?)
                GROUP BY (publication_id)
                HAVING COUNT(publication_id) = ?
            )
            AND p.id IN (SELECT publication_id FROM submitter_has_publication WHERE submitter_id = $userId)
                " . $orderQuery . $limitQuery;

        if ($limit) {
            $result = $this->database->query($query, $categories, count($categories), $limit, $offset)->fetchAll();
        } else {
            $result = $this->database->query($query, $categories, count($categories))->fetch();
        }

        return $result;
    }

    public function getAllPubs_Authors_advanced($keywords, $keywordsString, $sort, $limit = null, $offset = null) {
        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery_Author_OR($limit, $sort);
        $selectQuery = $this->getSelectQuery_Author_OR($limit);

        $conditions = '(';
        foreach ($keywords as $w) {
            if (strlen($w) < 3 && $w != "and")//jump over abbreviations
                continue;
            $conditions .= "a.name LIKE '%$w%' OR a.middlename LIKE '%$w%' OR a.surname LIKE '%$w%' OR ";
        }
        $conditions .= "p.title LIKE '%$keywordsString%' OR ";
        $conditions .= ' false)';

        $query = $selectQuery . "
            FROM publication p
            JOIN author_has_publication ap ON p.id = ap.publication_id
            JOIN author a ON a.id = ap.author_id
            WHERE $conditions
            AND p.id NOT IN (
              SELECT publication_id
              FROM categories_has_publication
              ) " . $orderQuery . $limitQuery;

        if ($limit) {
            $result = $this->database->query($query, $limit, $offset)->fetchAll();
        } else {
            $result = $this->database->query($query)->fetch();
        }

        return $result;
    }

    public function getAllPubs_Authors_advanced_starred_publication($keywords, $keywordsString, $sort, $userId, $limit = null, $offset = null) {
        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery_Author_OR($limit, $sort);
        $selectQuery = $this->getSelectQuery_Author_OR($limit);

        $conditions = '(';
        foreach ($keywords as $w) {
            if (strlen($w) < 3 && $w != "and")//jump over abbreviations
                continue;
            $conditions .= "a.name LIKE '%$w%' OR a.middlename LIKE '%$w%' OR a.surname LIKE '%$w%' OR ";
        }
        $conditions .= "p.title LIKE '%$keywordsString%' OR ";
        $conditions .= ' false)';

        $query = $selectQuery . "
           FROM publication p
           JOIN author_has_publication ap ON p.id = ap.publication_id
           JOIN author a ON a.id = ap.author_id
           WHERE $conditions
            AND p.id NOT IN (
                    SELECT publication_id
                    FROM categories_has_publication
            )
            AND p.id IN (SELECT publication_id FROM submitter_has_publication WHERE submitter_id = $userId)
                " . $orderQuery . $limitQuery;

        if ($limit) {
            $result = $this->database->query($query, $limit, $offset)->fetchAll();
        } else {
            $result = $this->database->query($query)->fetch();
        }

        return $result;
    }

    public function getAllPubs_Authors($keywords, $keywordsString, $sort, $limit = null, $offset = null) {
        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery_Author_OR($limit, $sort);
        $selectQuery = $this->getSelectQuery_Author_OR($limit);

        $conditions = '(';
        foreach ($keywords as $w) {
            if (strlen($w) < 3 && $w != "and")//jump over abbreviations
                continue;
            $conditions .= "a.name LIKE '%$w%' OR a.middlename LIKE '%$w%' OR a.surname LIKE '%$w%' OR ";
        }
        $conditions .= "p.title LIKE '%$keywordsString%' OR ";
        $conditions .= ' false)';

        $query = $selectQuery . "
            FROM publication p
            JOIN author_has_publication ap ON p.id = ap.publication_id
            JOIN author a ON a.id = ap.author_id
            WHERE $conditions" . $orderQuery . $limitQuery;

        if ($limit) {
            $result = $this->database->query($query, $limit, $offset)->fetchAll();
        } else {
            $result = $this->database->query($query)->fetch();
        }

        return $result;
    }

    public function getAllPubs_Authors_starred_publication($keywords, $keywordsString, $sort, $userId, $limit = null, $offset = null) {
        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery_Author_OR($limit, $sort);
        $selectQuery = $this->getSelectQuery_Author_OR($limit);

        $conditions = '(';
        foreach ($keywords as $w) {
            if (strlen($w) < 3 && $w != "and")//jump over abbreviations
                continue;
            $conditions .= "a.name LIKE '%$w%' OR a.middlename LIKE '%$w%' OR a.surname LIKE '%$w%' OR ";
        }
        $conditions .= "p.title LIKE '%$keywordsString%' OR ";
        $conditions .= ' false)';

        $query = $selectQuery . "
            FROM publication p
            JOIN author_has_publication ap ON p.id = ap.publication_id
            JOIN author a ON a.id = ap.author_id
            WHERE $conditions
            AND p.id IN (SELECT publication_id FROM submitter_has_publication WHERE submitter_id = $userId)" . $orderQuery . $limitQuery;

        if ($limit) {
            $result = $this->database->query($query, $limit, $offset)->fetchAll();
        } else {
            $result = $this->database->query($query)->fetch();
        }

        return $result;
    }

    /** @return \Nette\Database\Table\ActiveRow */
    public function getAllPubs_no_params($categories, $sort, $limit = null, $offset = null) {
        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery_Author_OR($limit, $sort);
        $selectQuery = $this->getSelectQuery_Author_OR($limit);

        $query = $selectQuery . "
            FROM publication p
            JOIN categories_has_publication c ON p.id = c.publication_id
            " . $orderQuery . $limitQuery;

        if ($limit) {
            $result = $this->database->query($query, $limit, $offset)->fetchAll();
        } else {
            $result = $this->database->query($query)->fetch();
        }

        return $result;
    }

    /** @return \Nette\Database\Table\ActiveRow */
    public function getAllPubs_Categories_OR($categories, $sort, $limit = null, $offset = null) {
        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery_Author_OR($limit, $sort);
        $selectQuery = $this->getSelectQuery_Author_OR($limit);

        $query = $selectQuery . "
            FROM publication p
            JOIN categories_has_publication c ON p.id = c.publication_id
            WHERE c.categories_id IN (?)
            " . $orderQuery . $limitQuery;

        if ($limit) {
            $result = $this->database->query($query, $categories, $limit, $offset)->fetchAll();
        } else {
            $result = $this->database->query($query, $categories)->fetch();
        }

        return $result;
    }

    /** @return \Nette\Database\Table\ActiveRow */
    public function getAllPubs_no_params_starred_publication($categories, $sort, $userId, $limit = null, $offset = null) {
        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery_Author_OR($limit, $sort);
        $selectQuery = $this->getSelectQuery_Author_OR($limit);

        $query = $selectQuery . "
          FROM publication p
          JOIN categories_has_publication c ON p.id = c.publication_id
          WHERE p.id IN (SELECT publication_id FROM submitter_has_publication WHERE submitter_id = $userId)
            " . $orderQuery . $limitQuery;

        if ($limit) {
            $result = $this->database->query($query, $limit, $offset)->fetchAll();
        } else {
            $result = $this->database->query($query)->fetch();
        }

        return $result;
    }

    /** @return \Nette\Database\Table\ActiveRow */
    public function getAllPubs_Categories_OR_starred_publication($categories, $sort, $userId, $limit = null, $offset = null) {
        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery_Author_OR($limit, $sort);
        $selectQuery = $this->getSelectQuery_Author_OR($limit);

        $query = $selectQuery . "
          FROM publication p
          JOIN categories_has_publication c ON p.id = c.publication_id
          WHERE c.categories_id IN (?)
          AND p.id IN (SELECT publication_id FROM submitter_has_publication WHERE submitter_id = $userId)
            " . $orderQuery . $limitQuery;

        if ($limit) {
            $result = $this->database->query($query, $categories, $limit, $offset)->fetchAll();
        } else {
            $result = $this->database->query($query, $categories)->fetch();
        }

        return $result;
    }

    public function getAllPubs_Categories_AND($categories, $sort, $limit = null, $offset = null) {
        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery_Author_OR($limit, $sort);
        $selectQuery = $this->getSelectQuery_Author_OR($limit);

        $query = $selectQuery . "
            FROM publication p
            WHERE p.id IN (
                            SELECT publication_id
                            FROM categories_has_publication
                            WHERE categories_id IN (?)
                            GROUP BY (publication_id)
                            HAVING COUNT(publication_id) = ?
                    ) " . $orderQuery . $limitQuery;

        if ($limit) {
            $result = $this->database->query($query, $categories, count($categories), $limit, $offset)->fetchAll();
        } else {
            $result = $this->database->query($query, $categories, count($categories))->fetch();
        }

        return $result;
    }

    public function getAllPubs_Categories_AND_starred_publication($categories, $sort, $userId, $limit = null, $offset = null) {
        $limitQuery = $this->getLimitQuery($limit);
        $orderQuery = $this->getOrderQuery_Author_OR($limit, $sort);
        $selectQuery = $this->getSelectQuery_Author_OR($limit);

        $query = $selectQuery . "
            FROM publication p
            WHERE p.id IN (
                            SELECT publication_id
                            FROM categories_has_publication
                            WHERE categories_id IN (?)
                            GROUP BY (publication_id)
                            HAVING COUNT(publication_id) = ?
                    )
                    AND p.id IN (SELECT publication_id from submitter_has_publication where submitter_id = $userId)
                " . $orderQuery . $limitQuery;

        if ($limit) {
            $result = $this->database->query($query, $categories, count($categories), $limit, $offset)->fetchAll();
        } else {
            $result = $this->database->query($query, $categories, count($categories))->fetch();
        }

        return $result;
    }

// IN VS OR OR OR OR
    public function getSelectQuery_Author_OR($limit) {
        $selectQuery = "";
        if ($limit) {
            $selectQuery = "SELECT DISTINCT p.id, p.pub_type, p.title, p.submitter_id, p.issue_year, p.issue_month ";
        } else {
            $selectQuery = "SELECT COUNT(DISTINCT p.id) AS length ";
        }
        return $selectQuery;
    }

    public function getOrderQuery_Author_OR($limit, $sort) {

        if (!$limit) {
            return "";
        }

        switch ($sort) {
            case "date":
                $order_clause = " ORDER BY p.issue_year DESC, p.issue_month DESC, p.title ASC ";
                break;
            default:
                $order_clause = " ORDER BY p.title ASC ";
        }

        return $order_clause;
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
     * @param int $id
     * @return array
     */
    public function findAllByUserId(int $id): array
    {
      return $this->database->fetchAll("SELECT p.* FROM publication p
                                        JOIN author_has_publication ap ON (p.id = ap.publication_id)
                                        JOIN author a ON (ap.author_id = a.id)
                                        WHERE a.user_id=?
                                        ORDER BY title ASC;",$id);
    }

}
