<?php

namespace App\Model;

class Reference extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'reference';

    public function findOneById($id) {
        return $this->findOneBy(array('id' => $id));
    }
    
    public function insertList($references, $publication_id, $submitter_id) {
        $rows = explode("\n", $references);
        $counter = 0;
        foreach ($rows as $row) {
            $data = trim($row);
            if (empty($data)) {
                continue;
            } else {
                $this->insert(array("text" => $data, 
                    "publication_id" => $publication_id,
                    "submitter_id" => $submitter_id));
            }
            $counter++;
        }
        return $counter;
        
    }

}

