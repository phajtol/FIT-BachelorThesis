<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;


class ConferenceYear extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'conference_year';

    /**
     * @var string
     */
    protected $conferenceTableName = 'conference';

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param string $kw
     * @return \Nette\Database\Table\Selection
     */
    public function findAllByKw(string $kw): Selection
    {
        $kwExpr = '%' . $kw . '%';

        return $this->findAll()->where($this->tableName . '.name LIKE ? OR ' .
            $this->tableName . '.abbreviation LIKE ? OR ' .
            $this->tableName . '.location LIKE ? OR ' .
            $this->conferenceTableName . '.name LIKE ? OR ' .
            $this->conferenceTableName . '.abbreviation LIKE ?'
        , [$kwExpr, $kwExpr, $kwExpr, $kwExpr, $kwExpr]);
    }

    /**
     * @param int $conferenceYearId
     */
    public function deleteAssociatedRecords($conferenceYearId): void
    {
        $workshops = $this->findAllBy(['parent_id' => $conferenceYearId]);

        foreach ($workshops as $workshop) {
            $workshop->update(['parent_id' => NULL]);
        }

        $this->database->table('conference_year_is_indexed')
            ->where(['conference_year_id' => $conferenceYearId])
            ->delete();

        $publications = $this->database->table('publication')
            ->where(["conference_year_id" => $conferenceYearId]);

        foreach ($publications as $pub) {
            $pub->update(['conference_year_id' => NULL]);
        }

        $conferenceYearTemp = $this->database->table('conference_year')->get($conferenceYearId);

        if ($conferenceYearTemp) {
            $conferenceYearTemp->delete();
        }
    }

    /**
     * @param int $conference_id
     * @return Selection
     */
    public function findAllByConferenceId(int $conference_id): Selection
    {
        return $this->findAllBy(['conference_id' => $conference_id]);
    }

    /**
     * @param int $id
     * @return FALSE|\Nette\Database\Table\ActiveRow
     */
    public function findOneById(int $id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @param string $name
     * @return ActiveRow
     */
    public function findOneByName(string $name)
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * @param \Nette\Database\Table\Selection $sel
     * @return \Nette\Database\Table\Selection
     */
    public function findOnlyLastYears(Selection $sel): Selection
    {
        $sel->group('`conference_year`.`conference_id`')
            ->select('`conference_year`.`conference_id` AS cf_id, max(`conference_year`.`w_year`) AS max_w_year');
        $sql = $sel->getSql();
        $cfyIds = [];

        $res = $this->database->queryArgs("SELECT id FROM `" . $this->tableName . "` ft INNER JOIN (" . $sql . ") pt
            ON pt.max_w_year = ft.w_year AND pt.cf_id = ft.conference_id", $sel->getSqlBuilder()->getParameters());

        foreach($res->fetchAll() as $row) {
            $cfyIds[] = $row->id;
        }

        return $this->findAll()
            ->where('id IN ?', $cfyIds)

                // extended functionality - exclude conferences that have some alive conference year
            ->where("conference_id NOT IN (SELECT distinct(conference_id) FROM `conference_year` WHERE state = ?)", 'alive');
    }

    // merged

    /**
     * @param int|null $conferenceId
     * @return array
     */
    public function getConferenceYearForSelectbox(?int $conferenceId): array
    {
        $conferenceYears = [];
        $conferenceYearsTemp = $this->database->table('conference_year')
            ->where(['conference_id' => $conferenceId])
            ->order('w_year ASC');

        foreach ($conferenceYearsTemp as $c) {
            $conferenceYears[$c->id] = ($c->w_year ? $c->w_year . ' (' . $c->name . ')' : $c->name);
        }

        return $conferenceYears;
    }

}
