<?php

namespace App\Model;

class Retrieve extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'retrieve';

    public function deleteByUserId($userId) {
        $this->findAllBy(array('submitter_id' => $userId))->delete();
    }

}
