<?php

namespace App\Model;

class UserSettings extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'user_settings';

    public function insertExplicit($user_id, $items_per_page, $deadline_notification_advance) {
        return $this->insert(array(
            'submitter_id'  =>  $user_id,
            'pagination'    =>  $items_per_page,
            'deadline_notification_advance' => $deadline_notification_advance
        ));
    }

}
