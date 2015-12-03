<?php

namespace App\Model;

class Publisher extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'publisher';

    public function findAllByKw($keywords) {
        return $this->database->table('publisher')->where("name LIKE ? OR address LIKE ?", "%" . $keywords . "%", "%" . $keywords . "%");
    }

    public function deleteWithAssociatedRecords($publisherId) {
        $publisher = $this->database->table('publication')->where(array("publisher_id" => $publisherId));
        foreach ($publisher as $pub) {
            $pub->update(array('publisher_id' => NULL));
        }

        $conferenceYear = $this->database->table('conference_year')->where(array("publisher_id" => $publisherId));
        foreach ($conferenceYear as $conf) {
            $conf->update(array('publisher_id' => NULL));
        }

        $record = $this->database->table('publisher')->get($publisherId);

        if ($record) {
            $record->delete();
            return $record;
        }
        return null;
    }

    public function findOneById($id) {
        return $this->findOneBy(array('id' => $id));
    }

    public function findOneByName($name) {
        return $this->findOneBy(array('name' => $name));
    }

}
