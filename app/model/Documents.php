<?php

namespace App\Model;


class Documents extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'documents';

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        $this->getTable()->where('publication_id', $id)->delete();
    }

    /**
     * @param $data
     * @return int
     */
    public function update($data): int
    {
        return $this->findAllBy(['publication_id' => $data['publication_id']])->update($data);
    }

}
