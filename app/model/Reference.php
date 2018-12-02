<?php

namespace App\Model;

use \App\Helpers\ReferenceParser;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;


class Reference extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'reference';

    /**
     * @var Publication
     */
    protected $publicationModel;

    /**
     * Reference constructor.
     * @param Publication $publicationModel
     * @param \Nette\Database\Context $db
     */
    public function __construct(Publication $publicationModel, \Nette\Database\Context $db)
    {
        parent::__construct($db);
        $this->publicationModel = $publicationModel;
    }

    /**
     * @param int $id
     * @return FALSE|\Nette\Database\Table\ActiveRow
     */
    public function findOneById(int $id)
    {
        return $this->find($id);
    }

    /**
     * @param $references
     * @param int $publication_id
     * @param int $submitter_id
     * @return int
     */
    public function insertList($references, int $publication_id, int $submitter_id): int
    {
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
                    "title" => $parser->getTitle(),
                    "processed" => new \DateTime())
                );
            }
            $counter++;
        }

        return $counter;
    }

    /**
     * @return \Nette\Database\Table\Selection
     */
    public function findAllUnconfirmed(): Selection
    {
      return $this->findAllBy(["confirmed" => 0]);
    }

    /**
     * @param int $id
     * @param int $reference_id
     */
    public function confirm(int $id, int $reference_id): void
    {
        $this->database->query("UPDATE reference set confirmed=1,reference_id=? where id=?", $reference_id, $id);
    }

    /**
     * @param int $id
     */
    public function refuse($id): void
    {
        $max_refused_id = $this->database->fetchField("select max(id) from pcublication;");
        $this->database->query("UPDATE reference set max_refused_id=? where id=?;",$max_refused_id, $id);
    }

    /**
     * @return array
     */
    public function findUnconfirmedWithPublication(): array
    {
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
          $data1 = $this->publicationModel->getAllPubInfo($publication1);
          $arr2['publication1_all_info'] = $data1['pubCit'];
          $arr2['publication1_all_info']['author_array'] = $data1['pubCit_author_array'];
          $arr2['publication1_all_info']['author'] = $data1['pubCit_author'];

          $arr2['publication2'] = $publication2;
          $data2 = $this->publicationModel->getAllPubInfo($publication2);
          $arr2['publication2_all_info'] = $data2['pubCit'];
          $arr2['publication2_all_info']['author_array'] = $data2['pubCit_author_array'];
          $arr2['publication2_all_info']['author'] = $data2['pubCit_author'];
          $arr2['reference'] = $reference;

          $arr[] = $arr2;
      }

      return $arr;
    }

    /**
     * @return int
     */
    public function process():int
    {
        $counter = 0;
        $references = $this->database->fetchAll("select * from reference where reference_id is null order by processed limit 50;");

        foreach ($references as $reference) {
            $parser = new ReferenceParser($reference->text);
            $parser->parse();
            $arr = ['id' => $reference->id, 'title' => $parser->getTitle(), 'processed' => new \DateTime()];
            $this->update($arr);
            $counter++;
        }

        return $counter;
    }
}
