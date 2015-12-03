<?php

namespace App\Model;

class Attributes extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'attributes';

    public function findAllByKw($kw) {
        return $this->database->table('attributes')->where("name LIKE ? OR description LIKE ?", "%" . $kw . "%", "%" . $kw . "%");
    }

    public function deleteAssociatedRecords($attributeId) {

        $attribute = $this->database->table('attrib_storage')->where(array("attributes_id" => $attributeId));
        foreach ($attribute as $attrib) {
            $attrib->delete();
        }

        $record = $this->database->table('attributes')->get($attributeId);
        if ($record) {
            $record->delete();
        }
    }

    public function findOneByName($name) {
        return $this->findOneBy(array('name' => $name));
    }

}
