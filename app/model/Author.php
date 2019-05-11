<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;


class Author extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'author';


    /**
     * Returns array with data to be show on author detail page.
     * @param int $id
     * @param bool $isAdmin
     * @return array
     */
    public function getAuthorWithHisTagsAndPublicationsAndStarred(int $id, bool $isAdmin): array
    {
        $res = [];
        $publicationIds = [];
        $author = $this->getTable()
            ->select('user_id, name, middlename, surname')
            ->where('id = ?', $id)
            ->fetch();

        $res['name'] = $author->name;
        $res['middlename'] = $author->middlename;
        $res['surname'] = $author->surname;

        if ($author->user_id) {
            //tags
            $params = [
                'submitter_id' => $author->user_id
            ];

            if (!$isAdmin) {
                $params['global_scope'] = 1;
            }

            $res['tags'] = $this->database->table('tag')
                ->select('id, name, global_scope')
                ->where($params);

            //starred
            $res['starred'] = $this->database->table('submitter_has_publication')
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
                publication.conference_year_id
                author_id')
                ->where('submitter_has_publication.submitter_id = ?', $author->user_id);

            foreach ($res['starred'] as $starred) {
                $publicationIds[] = $starred->id;
            }
        }

        $res['publications'] = $this->database->table('author_has_publication')
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
                publication.conference_year_id
                author_id')
            ->where('author_id = ?', $id)
            ->order('priority ASC');

        foreach ($res['publications'] as $publication) {
            $publicationIds[] = $publication->id;
        }

        $res['publicationAuthors'] = $this->getAuthorsByMultiplePubIds($publicationIds);

        return $res;
    }


    /**
     * @return array
     */
    public function getAuthorsNames(): array
    {
        $authors = $this->database->table('author')->order("surname ASC, name ASC");
        $authorsTemp = [];

        foreach ($authors as $author) {
            $authorsTemp[$author->id] = $author->surname . ', ' . ($author->name) . ($author->middlename ? ', ' . $author->middlename : '');
        }
        // Příjmení, jméno, 2. jméno. Teď je to příjmení, 2. jméno, jméno.

        return $authorsTemp;
    }

    /**
     * @return array
     */
    public function getAuthorsForAutocomplete(): array
    {
        $authors = $this->getTable()->order('surname ASC, name ASC');
        $res = [];

        foreach ($authors as $author) {
            $res[] = $author->name . ' ' . ($author->middlename ? $author->middlename . ' ' : '') . $author->surname;
        }

        return $res;
    }

    /**
     * @param string $surname
     * @param string $middlename
     * @param string $name
     * @return string
     */
    public function formNames(string $surname, string $middlename, string $name): string
    {
        return $surname . ' ' . ($middlename ? $middlename . ' ' : '') . ($name) . ", ";
    }

    /**
     * @param int $authorId
     */
    public function deleteAssociatedRecords(int $authorId): void {

        $related = $this->database->table('author_has_publication')->where(["author_id" => $authorId]);
        $record = $this->database->table('author')->get($authorId);

        foreach ($related as $rel) {
            $rel->delete();
        }

        if ($record) {
            $record->delete();
        }
    }

    /**
     * @param $keywords
     * @return \Nette\Database\Table\Selection
     */
    public function findAllByKw($keywords): Selection
    {
        return $this->fetchAll()->where("surname LIKE ? OR name LIKE ? OR middlename LIKE ?", "%" . $keywords . "%", "%" . $keywords . "%", "%" . $keywords . "%");
    }

    /**
     * @param int $pubId
     * @param string|null $sep
     * @param string|null $type
     * @return array|string
     */
    public function getAuthorsNamesByPubId(int $pubId, string $sep = null, string $type = null)
    {
        $authors = $this->database->table('author_has_publication')
            ->select('author.name, author.surname, author.middlename, author.id')
            ->where('publication_id', $pubId)
            ->order('priority ASC');
        $authorsTemp = [];
        $authorsMerged = '';

        switch ($type) {
            case "endnote":
            case "refworks":
                foreach ($authors as $author) {
                    $authorsTemp[$author['id']] = $author['surname'] . ', ' . ($author['name']) . ($author['middlename'] ? ' ' . $author['middlename'] : '');
                }
                break;

            case "bibtex":
                foreach ($authors as $author) {
                    $authorsTemp[$author['id']] = $author['surname'] . ' ' . ($author['middlename'] ? $author['middlename'] . ' ' : '') . ($author['name']);
                }
                break;

            default:
                foreach ($authors as $author) {
                    $authorsTemp[$author['id']] = $author['name'] . ' ' . ($author['middlename'] ? $author['middlename'] . ' ' : '') . ($author['surname']);
                }
        }

        if ($sep) {
            foreach ($authorsTemp as $author) {
                $authorsMerged .= $author;
                if (!($author == end($authorsTemp))) {
                    $authorsMerged .= $sep;
                }
            }
            $authorsTemp = $authorsMerged;
        }

        return $authorsTemp;
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getAuthorsByMultiplePubIds(array $ids): array
    {
        $res = [];
        $authors = $this->database->table('author_has_publication')
            ->select('publication_id, author.name, author.surname, author.middlename, author.id')
            ->where('publication_id IN ?', $ids)
            ->order('priority ASC');

        foreach ($authors as $author) {
            $res[$author->publication_id][] = [
                'id' => $author['id'],
                'surname' => $author['surname'],
                'middlename' => $author['middlename'],
                'name' => $author['name'],
                'initials' => mb_substr($author['name'], 0, 1) . '. ' .
                    ($author['middlename'] ? mb_substr($author['middlename'], 0, 1) . '. ' : '')
            ];
        }

        return $res;
    }

    /**
     * @param int $pubId
     * @return array
     */
    public function getAuthorsNamesByPubIdPure(int $pubId): array
    {
        $authors = $this->database->table('author_has_publication')
            ->select('author.name, author.surname, author.middlename, author.id')
            ->where('publication_id', $pubId)
            ->order('priority ASC');
        $authorsTemp = [];

        foreach ($authors as $author) {
            $authorsTemp[] = [
                'id' => $author['id'],
                'surname' => $author['surname'],
                'middlename' => $author['middlename'],
                'name' => $author['name'],
                'initials' => mb_substr($author['name'], 0, 1) . '. ' .
                    ($author['middlename'] ? mb_substr($author['middlename'], 0, 1) . '. ' : '')
            ];
        }

        return $authorsTemp;
    }

    /**
     * @param string $name
     * @param string $middlename
     * @param string $surname
     * @return bool|false|\Nette\Database\Table\ActiveRow|Selection
     */
    public function tryAllCombinations(string $name, string $middlename, string $surname)
    {
        $author = $this->database->table('author');

        if ($name) {
            $author = $author->where('name', $name);
        }
        if ($surname) {
            $author = $author->where('surname', $surname);
        }
        if ($middlename) {
            $author = $author->where('middlename', $middlename);
        }

        $author = $author->fetch();

        if ($author) {
            return $author;
        } else {
            $author = $this->database->table('author');
        }

        if (strlen($name) > 2) {
            $nameShort = mb_substr($name, 0, 1);
            $nameShort .= '.';
            $author = $author->where('name', $nameShort);
        } elseif (strlen($name) > 0 && strlen($name) <= 2) {
            $nameShort = mb_substr($name, 0, 1);
            $author = $author->where("name LIKE ?", $nameShort . "%");
        }

        if (strlen($middlename) > 2) {
            $middlenameShort = mb_substr($middlename, 0, 1);
            $middlenameShort .= '.';
            $author = $author->where('middlename', $middlenameShort);
        } elseif (strlen($middlename) > 0 && strlen($middlename) <= 2) {
            $middlenameShort = mb_substr($middlename, 0, 1);
            $author = $author->where("middlename LIKE ?", $middlenameShort . "%");
        }

        if ($surname) {
            $author = $author->where('surname', $surname);
        }

        $author = $author->fetch();

        if ($author) {
            return $author;
        }

        return false;
    }

    /**
     * @param string $name
     * @param string $middlename
     * @param string $surname
     * @return array|bool
     */
    public function getAuthorNameByAuthorName(string $name, string $middlename, string $surname)
    {
        $author = $this->tryAllCombinations($name, $middlename, $surname);
        if (!$author) {
            $author = $this->tryAllCombinations($name, '', $surname);
        }

        if (!$author) {
            $author = $this->tryAllCombinations($surname, '', $name);
        }

        if ($author) {
            return [
                'id' => $author->id,
                'name' => $author->surname . ', ' . ($author->name) . ($author->middlename ? ', ' . $author->middlename : '')
            ];
        }

        return false;
    }

    /**
     * A B C
     * A C B
     * B A C
     * B C A
     * C B A
     * C A B
     * @param string $name
     * @param string $middlename
     * @param string $surname
     * @return array|bool
     */
    public function getAuthorNameByAuthorName2(string $name, string $middlename, string $surname)
    {
        $author = $this->database->table('author')->where('name', $name)->where('middlename', $middlename)->where('surname', $surname)->fetch();

        if (!$author) {
            $author = $this->database->table('author')->where('name', $name)->where('middlename', $surname)->where('surname', $middlename)->fetch();
        }
        if (!$author) {
            $author = $this->database->table('author')->where('name', $middlename)->where('middlename', $name)->where('surname', $surname)->fetch();
        }
        if (!$author) {
            $author = $this->database->table('author')->where('name', $middlename)->where('middlename', $surname)->where('surname', $name)->fetch();
        }
        if (!$author) {
            $author = $this->database->table('author')->where('name', $surname)->where('middlename', $middlename)->where('surname', $name)->fetch();
        }
        if (!$author) {
            $author = $this->database->table('author')->where('name', $surname)->where('middlename', $name)->where('surname', $middlename)->fetch();
        }

        // ====

        if (!$author) {
            $author = $this->database->table('author')->where('name', $name)->where('surname', $surname)->fetch();
        }
        if (!$author) {
            $author = $this->database->table('author')->where('name', $name)->where('surname', $middlename)->fetch();
        }
        if (!$author) {
            $author = $this->database->table('author')->where('name', $surname)->where('surname', $name)->fetch();
        }
        if (!$author) {
            $author = $this->database->table('author')->where('name', $surname)->where('surname', $middlename)->fetch();
        }
        if (!$author) {
            $author = $this->database->table('author')->where('name', $middlename)->where('surname', $name)->fetch();
        }
        if (!$author) {
            $author = $this->database->table('author')->where('name', $middlename)->where('surname', $surname)->fetch();
        }

        if ($author) {
            return [
                'id' => $author->id,
                'name' => $author->surname . ' ' . ($author->middlename ? $author->middlename . ' ' : '') . ($author->name)
            ];
        }

        return false;
    }

    /**
     * @param int $id
     * @return string
     */
    public function getAuthorName(int $id): string
    {
        $author = $this->database->table('author')->get($id);
        return $author->surname . ', ' . ($author->name) . ($author->middlename ? ', ' . $author->middlename : '');
    }

    /**
     * @param string $name
     * @param string $middlename
     * @param string $surname
     * @return FALSE|\Nette\Database\Table\ActiveRow
     */
    public function findOneByName(string $name, string $middlename, string $surname)
    {
        return $this->findOneBy([
            'name' => $name,
            'middlename' => $middlename,
            'surname' => $surname
        ]);
    }


}
