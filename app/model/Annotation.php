<?php

namespace App\Model;


use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class Annotation extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'annotation';

    /**
     * @param int $publication_id
     * @param int $submitter_id
     * @return int
     */
    public function getAnnotationTag(int $publication_id, int $submitter_id): int
    {

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

    /**
     * @param int $publicationId
     * @param int $userId
     * @return \Nette\Database\Table\Selection
     */
    public function findAllForReaderOrSubmitter(int $publicationId, int $userId): Selection
    {
        return $this->database->table('annotation')
            ->where('publication_id', $publicationId)
            ->where("submitter_id = ? OR global_scope = ?", $userId, 1)
            ->order("id ASC");
    }

    /**
     * @param int $annotationId
     */
    public function deleteAssociatedRecords(int $annotationId): void
    {
        $record = $this->database->table('annotation')->where('id', $annotationId);

        if ($record) {
            $record->delete();
        }
    }

    /**
     * @param int $id
     * @return ActiveRow
     */
    public function find(int $id): ActiveRow
    {
        return $this->findOneBy(array('id' => $id));
    }

}
