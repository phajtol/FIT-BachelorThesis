<?php

namespace App\Model;


class AuthorHasPublication extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'author_has_publication';


    /**
     * @param int $authorId
     * @return array
     */
    public function getPublicationsByAuthor(int $authorId): array
    {
        return $this->getTable()
            ->select('publication_id')
            ->where(['author_id' => $authorId])
            ->order('priority ASC')
            ->fetchPairs('publication_id', 'publication_id');
    }

}
