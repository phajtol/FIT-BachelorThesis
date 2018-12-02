<?php

namespace App\Model;

use Nette\Database\Table\Selection;


class SubmitterHasCuGroup extends Base {

    /** @var string */
	protected $tableName = 'submitter_has_cu_group';


    /**
     * @param int $id
     * @return Selection
     */
	public function getAllByCuGroupId(int $id): Selection
    {
		return $this->findAllBy(['cu_group_id' => $id]);
	}

    /**
     * @param int $id
     * @return Selection
     */
	public function getAllByUserId(int $id): Selection
    {
		return $this->findAllBy(['submitter_id' => $id]);
	}


}