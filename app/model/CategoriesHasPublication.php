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

}
