<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;


class Journal extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'journal';

    /**
     * @param string $kw
     * @return \Nette\Database\Table\Selection
     */
    public function findAllByKw(string $kw): Selection
    {
        return $this->findAll()
            ->where("name LIKE ? OR issn LIKE ? OR doi LIKE ? OR abbreviation LIKE ?", [
                '%' . $kw . '%',
                '%' . $kw . '%',
                '%' . $kw . '%',
                '%' . $kw . '%'
            ]);
    }

    /**
     * @param int $journalId
     */
    public function deleteAssociatedRecords(int $journalId): void
    {
        $journal = $this->database->table('publication')->where(["journal_id" => $journalId]);

        foreach ($journal as $jour) {
            $jour->update(['journal_id' => NULL]);
        }

        $record = $this->database->table('journal')->get($journalId);

        if ($record) {
            $record->delete();
        }
    }

    /**
     * @return array
     */
    public function getJournalsNames(): array
    {
        $journals = $this->database->table('journal')->order('name ASC');
        $journalsTemp = [];

        foreach ($journals as $journal) {
            $journalsTemp[$journal->id] = $journal->name . ($journal->issn ? ", ISSN: $journal->issn" : '');
        }

        return $journalsTemp;
    }

    /**
     * @param string $name
     * @return FALSE|\Nette\Database\Table\ActiveRow
     */
    public function findOneByName(string $name)
    {
        return $this->findOneBy(["name" => $name]);
    }

}
