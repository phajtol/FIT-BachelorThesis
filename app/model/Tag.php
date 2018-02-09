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

    public function findAllForReaderOrSubmitter($publicationId, $userId) {
        return $this->getTable()
            ->where(':publication_has_tag.publication_id', $publicationId)
            ->where("submitter_id = ? OR global_scope = ?", $userId, 1)
            ->order("id ASC");
    }
    public function getPairs($userId) {
      return $this->getTable()
          ->where("submitter_id = ?", $userId)
          ->fetchPairs("id","name");
    }
}
