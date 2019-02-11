<?php

namespace App\Model;

class AttribStorage extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'attrib_storage';

    /**
     * @param $params
     */
    public function findAllByKw($params) {
        //wtf?
    }

    /**
     * @param $Id
     */
    public function deleteAssociatedRecords($Id) {
        //wtf?
    }


    /**
     * @param int $attribId
     * @return array
     */
    public function getPublicationsByAttribute(int $attribId): array
    {
        return $this->getTable()
            ->select('publication_id')
            ->where(['attributes_id' => $attribId])
            ->fetchPairs('publication_id', 'publication_id');
    }

}
