<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;


class Attributes extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'attributes';

    /**
     * @param string $kw
     * @return \Nette\Database\Table\Selection
     */
    public function findAllByKw(string $kw): Selection
    {
        return $this->database->table('attributes')
            ->where('name LIKE ? OR description LIKE ?', '%' . $kw . '%', '%' . $kw . '%');
    }

    /**
     * @param int $attributeId
     */
    public function deleteAssociatedRecords(int $attributeId): void {

        $attribute = $this->database->table('attrib_storage')->where(['attributes_id' => $attributeId]);
        $record = $this->database->table('attributes')->get($attributeId);

        foreach ($attribute as $attrib) {
            $attrib->delete();
        }

        if ($record) {
            $record->delete();
        }
    }

    /**
     * @param string $name
     * @return FALSE|\Nette\Database\Table\ActiveRow
     */
    public function findOneByName(string $name)
    {
        return $this->findOneBy(['name' => $name]);
    }

}
