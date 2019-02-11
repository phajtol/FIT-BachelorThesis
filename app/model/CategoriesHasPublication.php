<?php

namespace App\Model;

class CategoriesHasPublication extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'categories_has_publication';

    /**
     * @param $params
     */
    public function findAllByKw($params) {
        //wtf?
    }

    /**
     * @param $conferenceYearId
     */
    public function deleteAssociatedRecords(int $conferenceYearId): void {
        //wtf?
    }

    /**
     * @param int $category_id
     * @return array
     */
    public function getPublicationIdsByCategory(int $category_id): array
    {
        return $this->getTable()
            ->where('categories_id', $category_id)
            ->fetchPairs('publication_id', 'publication_id');
    }
}
