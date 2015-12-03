<?php

namespace App\Model;

class Author extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'author';

    public function getAuthorsNames() {
        $authors = $this->database->table('author')->order("surname ASC, name ASC");
        $authorsTemp = array();

        foreach ($authors as $author) {
            $authorsTemp[$author->id] = $author->surname . ', ' . ($author->name) . ($author->middlename ? ', ' . $author->middlename : '');
        }
        // Příjmení, jméno, 2. jméno. Teď je to příjmení, 2. jméno, jméno.
        return $authorsTemp;
    }

    public function formNames($surname, $middlename, $name) {
        return $surname . ' ' . ($middlename ? $middlename . ' ' : '') . ($name) . ", ";
    }

    public function deleteAssociatedRecords($authorId) {

        $related = $this->database->table('author_has_publication')->where(array("author_id" => $authorId));
        foreach ($related as $rel) {
            $rel->delete();
        }

        $record = $this->database->table('author')->get($authorId);
        if ($record) {
            $record->delete();
        }
    }

    public function findAllByKw($keywords) {
        return $this->fetchAll()->where("surname LIKE ? OR name LIKE ? OR middlename LIKE ?", "%" . $keywords . "%", "%" . $keywords . "%", "%" . $keywords . "%");
    }

    public function getAuthorsNamesByPubId($pubId, $sep = null, $type = null) {
        $authors = $this->database->table('author_has_publication')->where(array('publication_id' => $pubId))->order("priority ASC");
        $authorsTemp = array();
        $authorsMerged = '';

        switch ($type) {
            case "endnote":
            case "refworks":
                foreach ($authors as $author) {
                    $authorsTemp[$author->author->id] = $author->author->surname . ', ' . ($author->author->name) . ($author->author->middlename ? ' ' . $author->author->middlename : '');
                }
                break;
            case "bibtex":
                foreach ($authors as $author) {
                    $authorsTemp[$author->author->id] = $author->author->surname . ' ' . ($author->author->middlename ? $author->author->middlename . ' ' : '') . ($author->author->name);
                }
                break;

            default:
                foreach ($authors as $author) {
                    $authorsTemp[$author->author->id] = $author->author->surname . ', ' . ($author->author->name) . ($author->author->middlename ? ', ' . $author->author->middlename : '');
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

    public function getAuthorsNamesByPubIdPure($pubId) {
        $authors = $this->database->table('author_has_publication')->where(array('publication_id' => $pubId))->order("priority ASC");
        $authorsTemp = array();

        foreach ($authors as $author) {
            $authorsTemp[] = array(
                'surname' => $author->author->surname,
                'middlename' => $author->author->middlename,
                'name' => $author->author->name,
                'initials' => mb_substr($author->author->name, 0, 1) . '. ' . ($author->author->middlename ? mb_substr($author->author->middlename, 0, 1) . '. ' : ''),
            );
        }
        return $authorsTemp;
    }

    public function tryAllCombinations($name, $middlename, $surname) {

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

    public function getAuthorNameByAuthorName($name, $middlename, $surname) {

        $author = $this->tryAllCombinations($name, $middlename, $surname);
        if (!$author) {
            $author = $this->tryAllCombinations($name, '', $surname);
        }

        if (!$author) {
            $author = $this->tryAllCombinations($surname, '', $name);
        }

        if ($author) {
            return array('id' => $author->id, 'name' => $author->surname . ', ' . ($author->name) . ($author->middlename ? ', ' . $author->middlename : ''));
        }
        return false;
    }

    public function getAuthorNameByAuthorName2($name, $middlename, $surname) {

        /*
          A B C
          A C B
          B A C
          B C A
          C B A
          C A B
         */

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
            return array('id' => $author->id, 'name' => $author->surname . ' ' . ($author->middlename ? $author->middlename . ' ' : '') . ($author->name));
        }
        return false;
    }

    public function getAuthorName($id) {
        $author = $this->database->table('author')->get($id);
        return $author->surname . ', ' . ($author->name) . ($author->middlename ? ', ' . $author->middlename : '');
    }

    public function findOneByName($name, $middlename, $surname) {
        return $this->findOneBy(array('name' => $name, 'middlename' => $middlename, 'surname' => $surname));
    }


}
