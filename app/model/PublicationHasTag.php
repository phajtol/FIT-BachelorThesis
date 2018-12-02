<?php

namespace App\Model;


class PublicationHasTag extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'publication_has_tag';

    /**
     * @param int $id
     * @return int
     */
    public function deleteByTagId(int $id): int
    {
        return $this->getTable()->where(['tag_id' => $id ])->delete();
    }
}
