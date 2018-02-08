<?php

namespace App\Model;

class Tag extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'tag';


    public function findAllByUserId($user_id) {
        return $this->findAllBy(["submitter_id" => $user_id]);
    }

}
