<?php

namespace App\Model;

use \App\Helpers\ReferenceParser;

class Reference extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'reference';

    protected $publicationModel;

    public function __construct(Publication $publicationModel, \Nette\Database\Context $db) {
        parent::__construct($db);
        $this->publicationModel = $publicationModel;
    }

    public function findOneById($id) {
        return $this->find($id);
    }

    public function insertList($references, $publication_id, $submitter_id) {
        $rows = explode("\n", $references);
        $counter = 0;
        foreach ($rows as $row) {
            $data = trim($row);
            if (empty($data)) {
                continue;
            } else {
                $parser = new ReferenceParser($data);
                $parser->parse();
                $this->insert(array("text" => $data,
                    "publication_id" => $publication_id,
                    "submitter_id" => $submitter_id,
                    "title" => $parser->getTitle()));
            }
            $counter++;
        }
        return $counter;

    }

    public function findAllUnconfirmed() {
      return $this->findAllBy(["confirmed" => 0]);
    }

    public function confirm($id, $reference_id) {
        $this->database->query("UPDATE reference set confirmed=1,reference_id=? where id=?",$reference_id, $id);
    }

    public function refuse($id) {
        $max_refused_id = $this->database->fetchField("select max(id) from publication;");
        $this->database->query("UPDATE reference set max_refused_id=? where id=?;",$max_refused_id, $id);
    }

    public function findUnconfirmedWithPublication() {
      $unconfirmed = $this->findAllUnconfirmed();
      $arr = [];
      foreach ($unconfirmed as $reference) {
        $arr2 = [];
        $publication1 = $this->publicationModel->find($reference->publication_id);
        $publication2 = $this->publicationModel->findOneBy(["title" => $reference->title]);
        if (empty($publication2) || $reference->max_refused_id>$publication2->id) {
          continue;
        }
        $arr2['publication1'] = $publication1;
        $arr2['publication2'] = $publication2;
        $arr2['reference'] = $reference;
        $arr[] = $arr2;
      }
      return $arr;
    }
}
