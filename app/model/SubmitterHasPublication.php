<?php

namespace App\Model;

class SubmitterHasPublication extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'submitter_has_publication';

    public function findAllStarredByKwFilter($params, $userId) {
        $records = $this->database->table('submitter_has_publication')->where("submitter_has_publication.submitter_id", $userId);
        if (isset($params['keywords'])) {
            $records = $records->where("publication.title LIKE ?", "%" . $params['keywords'] . "%");
        }
        if (isset($params['filter']) && $params['filter'] != 'none') {
            $records = $records->where("publication.title LIKE ?", $params['filter'] . "%");
        }
        $records = $records->order('publication.' . $params['sort'] . ' ' . $params['order']);

        return $records;
    }

}
