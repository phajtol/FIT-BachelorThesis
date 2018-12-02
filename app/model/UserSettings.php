<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;


class UserSettings extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'user_settings';

    /**
     * @param int $user_id
     * @param int $items_per_page
     * @param int $deadline_notification_advance
     * @return ActiveRow
     */
    public function insertExplicit(int $user_id, int $items_per_page, int $deadline_notification_advance): ActiveRow
    {
        return $this->insert([
            'submitter_id'  =>  $user_id,
            'pagination'    =>  $items_per_page,
            'deadline_notification_advance' => $deadline_notification_advance
        ]);
    }

}
