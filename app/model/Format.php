<?php

namespace App\Model;

class Format extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'format';

    public function findAllByKw($params) {

        $records = $this->database->table('format');
        if (isset($params['keywords'])) {
            $records = $records->where("name LIKE ?", "%" . $params['keywords'] . "%");
        }
        if (isset($params['filter']) && $params['filter'] != 'none') {
            $records = $records->where("name LIKE ?", $params['filter'] . "%");
        }
        $records = $records->order($params['sort'] . ' ' . $params['order']);

        return $records;
    }

    public function deleteAssociatedRecords($formatId) {

        $record = $this->database->table('format')->get($formatId);

        if ($record) {
            $record->delete();
        }
    }

    public function findOneByName($name) {
        return $this->findOneBy(array('name' => $name));
    }

}
