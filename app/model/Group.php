<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;


class Group extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'group';

    /**
     * @param array $params
     * @return \Nette\Database\Table\Selection
     */
    public function findAllByKw(array $params): Selection
    {
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

    /**
     * @param int $groupId
     */
    public function deleteAssociatedRecords(int $groupId): void
    {
        $related = $this->database->table('group_has_publication')->where(["group_id" => $groupId]);
        $related2 = $this->database->table('submitter_has_group')->where(["group_id" => $groupId]);
        $record = $this->database->table('group')->get($groupId);

        foreach ($related as $rel) {
            $rel->delete();
        }

        foreach ($related2 as $rel) {
            $rel->delete();
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
        return $this->findOneBy([
            'name'  =>  $name
        ]);
    }
}
