<?php

namespace App\Model;

class Annotation extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'annotation';

    public function getAnnotationTag($publication_id, $submitter_id) {

        $indicator = 0;
        /* if ($this->database->table('annotation')->where(array('publication_id' => $publication_id, 'submitter_id' => $submitter_id))->fetch()) {
            $indicator = 2;
        } elseif ($this->database->table('annotation')->where(array('publication_id' => $publication_id, 'global_scope' => 1))->fetch()) {
            $indicator = 1;
        } else {
            $indicator = 0;
        } */

        return $indicator;
    }

    public function findAllForReaderOrSubmitter($publicationId, $userId) {
        return $this->database->table('annotation')->where('publication_id', $publicationId)->where("submitter_id = ? OR global_scope = ?", $userId, 1)->order("id ASC");
    }

    public function deleteAssociatedRecords($annotationId) {
        $record = $this->database->table('annotation')->where('id', $annotationId);

        if ($record) {
            $record->delete();
        }
    }

    public function find($id){
        return $this->findOneBy(array('id' => $id));
    }

}
