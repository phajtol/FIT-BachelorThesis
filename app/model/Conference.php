<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;


class Conference extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'conference';

    /** @var string */
    protected $hasAcmCategoriesTable = 'conference_has_acm_category';

    /** @var string */
    protected $hasCategoriesTable = 'conference_has_category';

    /** @var string */
    protected $userFavouritesTable = 'submitter_favourite_conference';

    /** @var string */
    protected $cyTable = 'conference_year';

    /** @var string */
    protected $isIndexedTable = 'conference_year_is_indexed';

    /** @var string */
    protected $publicationTable = 'publication';


    /**
     * @param string $kw
     * @return \Nette\Database\Table\Selection
     */
    public function findAllByKw(string $kw): Selection
    {
        return $this->findAll()
            ->where("name LIKE ? OR abbreviation LIKE ? OR description LIKE ? OR first_year LIKE ?",
                "%" . $kw . "%",
                "%" . $kw . "%",
                "%" . $kw . "%",
                "%" . $kw . "%"
            );
    }

    /**
     * @param int $conferenceId
     */
    public function deleteAssociatedRecords(int $conferenceId): void
    {
        $this->database->table('conference_has_acm_category')->where(['conference_id' => $conferenceId])->delete();
        $this->database->table('conference_has_category')->where(['conference_id' => $conferenceId])->delete();
        $this->database->table('submitter_favourite_conference')->where(['conference_id' => $conferenceId])->delete();
        $conferenceRelated = $this->database->table('conference_year')->where(['conference_id' => $conferenceId]);

        foreach ($conferenceRelated as $row) {
            $this->database->table('conference_year_is_indexed')->where(['conference_year_id' => $row->id])->delete();
            $this->database->table('conference_year')->where(array('parent_id' => $row->id))->update(array('parent_id' => NULL));

            $publications = $this->database->table('publication')->where(array("conference_year_id" => $row['id']));

            foreach ($publications as $pub) {
                $pub->update(['conference_year_id' => NULL]);
            }

            $row->delete();
        }

        $record = $this->database->table('conference')->get($conferenceId);

        if ($record) {
            $record->delete();
        }
    }

    /**
     * @param int $old_id
     * @param int $new_id
     * @throws \Exception
     */
    public function mergeConferences($old_id, $new_id) {

        // todo check transaction handling right there?
        $this->database->beginTransaction();

        try {
            // transfer categories
            $has_acm_categories = $this->database->table($this->hasAcmCategoriesTable)->where(['conference_id' => $old_id]);
            $has_categories = $this->database->table($this->hasCategoriesTable)->where(['conference_id' => $old_id]);

            foreach ($has_acm_categories as $has_acm_category) {
                $data = ['conference_id' => $new_id, 'acm_category_id' => $has_acm_category->acm_category_id];

                if ($this->database->table($this->hasAcmCategoriesTable)->where($data)->count() == 0) {
                    $this->database->table($this->hasAcmCategoriesTable)->insert($data);
                }
            }

            foreach ($has_categories as $has_category) {
                $data = ['conference_id' => $new_id, 'conference_category_id' => $has_category->conference_category_id];

                if ($this->database->table($this->hasCategoriesTable)->where($data)->count() == 0) {
                    $this->database->table($this->hasCategoriesTable)->insert($data);
                }
            }

            // transfer favourites
            $user_favourites = $this->database->table($this->userFavouritesTable)->where(['conference_id' => $old_id]);

            foreach ($user_favourites as $user_favourite) {
                $data = ['conference_id' => $new_id, 'submitter_id' => $user_favourite->submitter_id];

                if ($this->database->table($this->userFavouritesTable)->where($data)->count() == 0) {
                    $this->database->table($this->userFavouritesTable)->insert($data);
                }
            }

            // transfer years
            $old_cys = $this->database->table($this->cyTable)->where(['conference_id' => $old_id]);
            $new_cys = $this->database->table($this->cyTable)->where(['conference_id' => $new_id]);
            $new_cys_by_year = [];

            foreach ($new_cys as $new_cy) {
                if(!is_null($new_cy->w_year) && $new_cy->w_year) {
                    $new_cys_by_year[$new_cy->w_year] = $new_cy;
                }
            }

            foreach ($old_cys as $old_cy) {
                if (isset($new_cys_by_year[$old_cy->w_year])) {  // we have to merge conference years
                    $new_cy = $new_cys_by_year[$old_cy->w_year];

                    // transfer missing columns
                    $columns2transfer = ['name', 'abbreviation', 'w_from', 'w_to', 'deadline', 'notification', 'finalversion',
                        'location', 'isbn', 'description', 'publisher_id', 'doi', 'issn', 'web', 'parent_id', 'submitter_id'];
                    $updArray = [];

                    foreach ($columns2transfer as $column2transfer) {
                        if ((is_null($new_cy->$column2transfer) || $new_cy->$column2transfer === '')
                            && (isset($old_cy->$column2transfer))) {
                            $updArray[$column2transfer] = $old_cy->$column2transfer;
                        }
                    }
                    if (count($updArray)) $this->database->table($this->cyTable)->where(['id' => $new_cy->id])->update($updArray);

                    // associate indexes
                    $is_indexed_records = $this->database->table($this->isIndexedTable)->where(['conference_year_id' => $old_cy->id]);

                    foreach ($is_indexed_records as $is_indexed_record) {
                        $data = ['conference_year_id' => $new_cy->id, 'document_index_id' => $is_indexed_record->document_index_id];

                        if ($this->database->table($this->isIndexedTable)->where($data)->count() == 0) {
                            $this->database->table($this->isIndexedTable)->insert($data);
                        }
                    }

                    // associate publications
                    $this->database->table($this->publicationTable)
                        ->where(['conference_year_id' => $old_cy->id])
                        ->update(['conference_year_id' => $new_cy->id]);

                } else {    // just associate the conference year with the new conference
                    $this->database->table($this->cyTable)
                        ->where(['id' => $old_cy->id])
                        ->update(['conference_id' => $new_id]);
                }
            }

            $this->deleteAssociatedRecords($old_id);
            $this->database->commit();
        } catch (\Exception $e) {
            $this->database->rollBack();
            throw $e;
        }
    }

    /**
     * @param int $user_id
     * @return array
     */
    public function getUserFavouriteConferences(int $user_id): array
    {
        $favs = $this->database->table($this->userFavouritesTable)->where(["user_id" => $user_id]);
        $conferences = [];

        foreach($favs as $fav) {
            $conferences[] = $fav->ref('conference');
        }

        return $conferences;
    }

    /**
     * @param string $name
     * @return FALSE|\Nette\Database\Table\ActiveRow
     */
    public function getConferenceByName(string $name)
    {
        return $this->findOneBy(['name'   =>  $name]);
    }

    /**
     * @param string $abbr
     * @return FALSE|ActiveRow
     */
    public function getConferenceByAbbreviation(string $abbr)
    {
        return $this->findOneBy(['abbreviation' => $abbr]);
    }

    // merged
    /**
     * @return array
     */
    public function getConferenceForSelectbox(): array {
        $conferences = [];
        $conferencesTemp = $this->database->table('conference')->order('abbreviation ASC');

        foreach ($conferencesTemp as $c) {
            $conferences[$c->id] = ($c->abbreviation ? $c->abbreviation . ' (' . $c->name . ')' : $c->name);
        }

        return $conferences;
    }

}
