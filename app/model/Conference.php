<?php

namespace App\Model;

class Conference extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'conference';

    protected $hasAcmCategoriesTable = 'conference_has_acm_category';
    protected $hasCategoriesTable = 'conference_has_category';
    protected $userFavouritesTable = 'submitter_favourite_conference';
    protected $cyTable = 'conference_year';
    protected $isIndexedTable = 'conference_year_is_indexed';
    protected $publicationTable = 'publication';


    public function findAllByKw($kw) {
        return $this->findAll()->where("name LIKE ? OR abbreviation LIKE ? OR description LIKE ? OR first_year LIKE ?", "%" . $kw . "%", "%" . $kw . "%", "%" . $kw . "%", "%" . $kw . "%");
    }

    public function deleteAssociatedRecords($conferenceId) {

        $this->database->table('conference_has_acm_category')->where(array('conference_id' => $conferenceId))->delete();
        $this->database->table('conference_has_category')->where(array('conference_id' => $conferenceId))->delete();
        $this->database->table('submitter_favourite_conference')->where(array('conference_id' => $conferenceId))->delete();

        $conferenceRelated = $this->database->table('conference_year')->where(array("conference_id" => $conferenceId));

        foreach ($conferenceRelated as $row) {

            $this->database->table('conference_year_is_indexed')->where(array('conference_year_id' => $row->id))->delete();
            $this->database->table('conference_year')->where(array('parent_id' => $row->id))->update(array('parent_id' => NULL));

            $publications = $this->database->table('publication')->where(array("conference_year_id" => $row['id']));

            foreach ($publications as $pub) {
                $pub->update(array('conference_year_id' => NULL));
            }
            $row->delete();
        }

        $record = $this->database->table('conference')->get($conferenceId);

        if ($record) {
            $record->delete();
        }
    }

    public function mergeConferences($old_id, $new_id) {

        // todo check transaction handling right there?
        $this->database->beginTransaction();

        try {

            // transfer categories
            $has_acm_categories = $this->database->table($this->hasAcmCategoriesTable)->where(array('conference_id' => $old_id));
            $has_categories = $this->database->table($this->hasCategoriesTable)->where(array('conference_id' => $old_id));
            foreach ($has_acm_categories as $has_acm_category) {
                $data = array('conference_id' => $new_id, 'acm_category_id' => $has_acm_category->acm_category_id);
                if ($this->database->table($this->hasAcmCategoriesTable)->where($data)->count() == 0) $this->database->table($this->hasAcmCategoriesTable)->insert($data);
            }
            foreach ($has_categories as $has_category) {
                $data = array('conference_id' => $new_id, 'conference_category_id' => $has_category->conference_category_id);
                if ($this->database->table($this->hasCategoriesTable)->where($data)->count() == 0) $this->database->table($this->hasCategoriesTable)->insert($data);
            }

            // transfer favourites
            $user_favourites = $this->database->table($this->userFavouritesTable)->where(array('conference_id' => $old_id));
            foreach ($user_favourites as $user_favourite) {
                $data = array('conference_id' => $new_id, 'submitter_id' => $user_favourite->submitter_id);
                if ($this->database->table($this->userFavouritesTable)->where($data)->count() == 0) $this->database->table($this->userFavouritesTable)->insert($data);
            }

            // transfer years
            $old_cys = $this->database->table($this->cyTable)->where(array('conference_id' => $old_id));
            $new_cys = $this->database->table($this->cyTable)->where(array('conference_id' => $new_id));

            $new_cys_by_year = array();
            foreach ($new_cys as $new_cy) {
                if(!is_null($new_cy->w_year) && $new_cy->w_year) $new_cys_by_year[$new_cy->w_year] = $new_cy;
            }

            foreach ($old_cys as $old_cy) {
                if (isset($new_cys_by_year[$old_cy->w_year])) {  // we have to merge conference years

                    $new_cy = $new_cys_by_year[$old_cy->w_year];

                    // transfer missing columns
                    $columns2transfer = array('name', 'abbreviation', 'w_from', 'w_to', 'deadline', 'notification', 'finalversion',
                        'location', 'isbn', 'description', 'publisher_id', 'doi', 'issn', 'web', 'parent_id', 'submitter_id');

                    $updArray = [];
                    foreach ($columns2transfer as $column2transfer) {
                        if ((is_null($new_cy->$column2transfer) || $new_cy->$column2transfer === '')
                            && (isset($old_cy->$column2transfer))
                        ) {
                            $updArray[$column2transfer] = $old_cy->$column2transfer;
                        }
                    }
                    if (count($updArray)) $this->database->table($this->cyTable)->where(array('id' => $new_cy->id))->update($updArray);

                    // associate indexes
                    $is_indexed_records = $this->database->table($this->isIndexedTable)->where(array('conference_year_id' => $old_cy->id));
                    foreach ($is_indexed_records as $is_indexed_record) {
                        $data = array('conference_year_id' => $new_cy->id, 'document_index_id' => $is_indexed_record->document_index_id);
                        if ($this->database->table($this->isIndexedTable)->where($data)->count() == 0) $this->database->table($this->isIndexedTable)->insert($data);
                    }

                    // associate publications
                    $this->database->table($this->publicationTable)->where(array('conference_year_id' => $old_cy->id))
                        ->update(array('conference_year_id' => $new_cy->id));

                } else {    // just associate the conference year with the new conference
                    $this->database->table($this->cyTable)->where(array('id' => $old_cy->id))->update(array(
                        'conference_id' => $new_id
                    ));
                }
            }

            $this->deleteAssociatedRecords($old_id);

            $this->database->commit();

        } catch (\Exception $e) {
            $this->database->rollBack();
            throw $e;
        }
    }

    public function getUserFavouriteConferences($user_id) {
        $favs = $this->database->table($this->userFavouritesTable)->where(
            array("user_id"     =>      $user_id)
        );

        $conferences = array();
        foreach($favs as $fav) {
            $conferences[] = $fav->ref('conference');
        }

        return $conferences;
    }

    public function getConferenceByName($name){
        return $this->findOneBy(array('name'   =>  $name));
    }

    public function getConferenceByAbbreviation($abbr) {
        return $this->findOneBy(array('abbreviation' => $abbr));
    }

    // merged
    public function getConferenceForSelectbox() {
        $conferences = array();
        $conferencesTemp = $this->database->table('conference')->order("abbreviation ASC");

        foreach ($conferencesTemp as $c) {
            $conferences[$c->id] = ($c->abbreviation ? $c->abbreviation . ' (' . $c->name . ')' : $c->name);
        }

        return $conferences;
    }

}
