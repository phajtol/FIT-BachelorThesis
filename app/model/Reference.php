<?php

namespace App\Model;

class Reference extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'reference';

    public function findOneById($id) {
        return $this->findOneBy(array('id' => $id));
    }

}

