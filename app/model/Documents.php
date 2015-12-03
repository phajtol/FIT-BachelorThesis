<?php

namespace App\Model;

class Documents extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'documents';

    public function delete($id) {
        $this->getTable()->where('publication_id', $id)->delete();
    }

    public function update($data) {
        return $this->findAllBy(array('publication_id' => $data['publication_id']))->update($data);
    }


}
