<?php

namespace App\Model;

use Nette\Database\Table\Selection;


class SubmitterHasGroup extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'submitter_has_group';

    /**
     * @param array $params
     * @param int $userId
     * @return \Nette\Database\Table\Selection
     */
    public function findAllStarredByKwFilter(array $params, int $userId): Selection
    {
        $records = $this->database->table('submitter_has_group')->where('submitter_has_group.submitter_id', $userId);

        if (isset($params['keywords'])) {
            $records = $records->where('group.name LIKE ? ', '%' . $params['keywords'] . '%');
        }
        if (isset($params['filter']) && $params['filter'] != 'none') {
            $records = $records->where('group.name LIKE ?', $params['filter'] . '%');
        }
        $records = $records->order('group.' . $params['sort'] . ' ' . $params['order']);

        return $records;
    }

}
