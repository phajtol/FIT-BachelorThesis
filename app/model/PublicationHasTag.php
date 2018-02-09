<?php

namespace App\Model;

class PublicationHasTag extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'publication_has_tag';

    public function deleteByTagId($id) {
      $this->getTable()->where(['tag_id' => $id ])->delete();
    }

}
