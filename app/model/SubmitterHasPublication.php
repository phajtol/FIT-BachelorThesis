<?php

namespace App\Model;

use Nette\Database\Table\Selection;


class SubmitterHasPublication extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'submitter_has_publication';

    /**
     * @param array $params
     * @param int $userId
     * @return \Nette\Database\Table\Selection
     */
    public function findAllStarredByKwFilter(array $params, int $userId): Selection {
        $records = $this->database->table('submitter_has_publication')
            ->where('submitter_has_publication.submitter_id', $userId);

        if (isset($params['keywords'])) {
            $records = $records->where('publication.title LIKE ?', '%' . $params['keywords'] . '%');
        }
        if (isset($params['filter']) && $params['filter'] != 'none') {
            $records = $records->where('publication.title LIKE ?', $params['filter'] . '%');
        }

        $records = $records->order('publication.' . $params['sort'] . ' ' . $params['order']);

        return $records;
    }

}
