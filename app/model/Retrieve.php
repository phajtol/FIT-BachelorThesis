<?php

namespace App\Model;


class Retrieve extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'retrieve';

    /**
     * @param int $userId
     * @return int - affected rows
     */
    public function deleteByUserId(int $userId): int
    {
        return $this->findAllBy(['submitter_id' => $userId])->delete();
    }

}
