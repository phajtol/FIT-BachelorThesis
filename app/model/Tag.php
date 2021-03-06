<?php

namespace App\Model;

use Nette\Database\Table\Selection;

class Tag extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'tag';

    /**
     * @param int $user_id
     * @return Selection
     */
    public function findAllByUserId(int $user_id): Selection
    {
        return $this->findAllBy(["submitter_id" => $user_id]);
    }

    /**
     * @param int $publicationId
     * @param int $userId
     * @return Selection
     */
    public function findAllForReaderOrSubmitter(int $publicationId, int $userId): Selection
    {
        return $this->getTable()
            ->where(':publication_has_tag.publication_id', $publicationId)
            ->where("submitter_id = ? OR global_scope = ?", $userId, 1)
            ->order("id ASC");
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getPairs(int $userId): array
    {
      return $this->getTable()
          ->where("submitter_id = ?", $userId)
          ->fetchPairs("id","name");
    }

    /**
     * Returns tags in format for HomepageSearchForm, t.j. array where key is tag id and value is tag name.
     * @param int $userId
     * @return array
     */
    public function getTagsForSearchForm(int $userId): array
    {
        return $this->getTable()
            ->select('id, name, global_scope')
            ->whereOr([
                'submitter_id' => $userId,
                'global_scope' => 1
            ])->fetchPairs('id', 'name');
    }
}
