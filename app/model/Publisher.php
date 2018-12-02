<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class Publisher extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'publisher';

    /**
     * @param string $keywords
     * @return \Nette\Database\Table\Selection
     */
    public function findAllByKw(string $keywords): Selection
    {
        return $this->database->table('publisher')->where('name LIKE ? OR address LIKE ?', '%' . $keywords . '%', '%' . $keywords . '%');
    }

    /**
     * @param int $publisherId
     * @return false|\Nette\Database\Table\ActiveRow|null
     */
    public function deleteWithAssociatedRecords(int $publisherId): ?ActiveRow
    {
        $publisher = $this->database->table('publication')->where(['publisher_id' => $publisherId]);

        foreach ($publisher as $pub) {
            $pub->update(['publisher_id' => NULL]);
        }

        $conferenceYear = $this->database->table('conference_year')->where(['publisher_id' => $publisherId]);

        foreach ($conferenceYear as $conf) {
            $conf->update(['publisher_id' => NULL]);
        }

        $record = $this->database->table('publisher')->get($publisherId);

        if ($record) {
            $record->delete();
            return $record;
        }
        return null;
    }

    /**
     * @param int $id
     * @return ActiveRow
     */
    public function findOneById(int $id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @param string $name
     * @return FALSE|ActiveRow
     */
    public function findOneByName(string $name)
    {
        return $this->findOneBy(['name' => $name]);
    }

}
