<?php

namespace App\Model;

class Journal extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'journal';

    public function findAllByKw($kw) {
        return $this->findAll()->where("name LIKE ? OR issn LIKE ? OR doi LIKE ? OR abbreviation LIKE ?", array('%' . $kw . '%', '%' . $kw . '%', '%' . $kw . '%', '%' . $kw . '%'));
    }

    public function deleteAssociatedRecords($journalId) {

        $journal = $this->database->table('publication')->where(array("journal_id" => $journalId));
        foreach ($journal as $jour) {
            $jour->update(array('journal_id' => NULL));
        }

        $record = $this->database->table('journal')->get($journalId);

        if ($record) {
            $record->delete();
        }
    }

    public function getJournalsNames() {
        $journals = $this->database->table('journal')->order("name ASC");
        $journalsTemp = array();

        foreach ($journals as $journal) {
            $journalsTemp[$journal->id] = $journal->name . ($journal->issn ? ", ISSN: $journal->issn" : '');
        }

        return $journalsTemp;
    }

    public function findOneByName($name) {
        return $this->findOneBy(array("name" => $name));
    }

}
