<?php

namespace App\Model;

class ConferenceYear extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'conference_year';

    protected $conferenceTableName = 'conference';

    public function getTableName(){
        return $this->tableName;
    }

    public function findAllByKw($kw) {
        $kwExpr = '%' . $kw . '%';
        return $this->findAll()->where($this->tableName . ".name LIKE ? OR " .
            $this->tableName . ".abbreviation LIKE ? OR " .
            $this->tableName . ".location LIKE ? OR " .
            $this->conferenceTableName . ".name LIKE ? OR " .
            $this->conferenceTableName . ".abbreviation LIKE ?"
        , array($kwExpr, $kwExpr, $kwExpr, $kwExpr, $kwExpr));
    }

    public function deleteAssociatedRecords($conferenceYearId) {

        $workshops = $this->findAllBy(array('parent_id' => $conferenceYearId));
        foreach($workshops as $workshop) $workshop->update(array('parent_id'    => NULL));

        $this->database->table('conference_year_is_indexed')->where(array('conference_year_id' => $conferenceYearId))->delete();

        $publications = $this->database->table('publication')->where(array("conference_year_id" => $conferenceYearId));
        foreach ($publications as $pub) {
            $pub->update(array('conference_year_id' => NULL));
        }

        $conferenceYearTemp = $this->database->table('conference_year')->get($conferenceYearId);
        if ($conferenceYearTemp) {
            $conferenceYearTemp->delete();
        }
    }

    public function findAllByConferenceId($conference_id) {
        return $this->findAllBy(array('conference_id' => $conference_id));
    }

    public function findOneById($id) {
        return $this->findOneBy(array('id' => $id));
    }

    public function findOneByName($name) {
        return $this->findOneBy(array('name' => $name));
    }

    /**
     * @param \Nette\Database\Table\Selection $sel
     * @return \Nette\Database\Table\Selection
     */
    public function findOnlyLastYears(\Nette\Database\Table\Selection $sel){
        $sel->group('`conference_year`.`conference_id`')
            ->select('`conference_year`.`conference_id` AS cf_id, max(`conference_year`.`w_year`) AS max_w_year');

        $sql = $sel->getSql();

        $res = $this->database->queryArgs("SELECT id FROM `" . $this->tableName . "` ft INNER JOIN (" . $sql . ") pt
            ON pt.max_w_year = ft.w_year AND pt.cf_id = ft.conference_id", $sel->getSqlBuilder()->getParameters());

        $cfyIds = array();
        foreach($res->fetchAll() as $row) {
            $cfyIds[] = $row->id;
        }


        return $this->findAll()
            ->where('id IN ?', $cfyIds)

                // extended functionality - exclude conferences that have some alive conference year
            ->where("conference_id NOT IN (SELECT distinct(conference_id) FROM `conference_year` WHERE state = ?)", 'alive');
    }

    // merged
    public function getConferenceYearForSelectbox($conferenceId) {
        $conferenceYears = array();
        $conferenceYearsTemp = $this->database->table('conference_year')->where(array("conference_id" => $conferenceId))->order("w_year ASC");

        foreach ($conferenceYearsTemp as $c) {
            $conferenceYears[$c->id] = ($c->w_year ? $c->w_year . ' (' . $c->name . ')' : $c->name);
        }

        return $conferenceYears;
    }

}
