<?php

namespace App\Model;

class Group extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'group';

    public function findAllByKw($params) {

        $records = $this->database->table('group');
        if (isset($params['keywords'])) {
            $records = $records->where("name LIKE ?", "%" . $params['keywords'] . "%");
        }
        if (isset($params['filter']) && $params['filter'] != 'none') {
            $records = $records->where("name LIKE ?", $params['filter'] . "%");
        }
        $records = $records->order($params['sort'] . ' ' . $params['order']);

        return $records;
    }

    public function deleteAssociatedRecords($groupId) {

        $related = $this->database->table('group_has_publication')->where(array("group_id" => $groupId));
        foreach ($related as $rel) {
            $rel->delete();
        }

        $related2 = $this->database->table('submitter_has_group')->where(array("group_id" => $groupId));
        foreach ($related2 as $rel) {
            $rel->delete();
        }

        $record = $this->database->table('group')->get($groupId);
        if ($record) {
            $record->delete();
        }
    }

    public function findOneByName($name) {
        return $this->findOneBy(array(
            'name'  =>  $name
        ));
    }

}
