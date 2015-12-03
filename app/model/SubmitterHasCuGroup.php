<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.4.2015
 * Time: 2:16
 */

namespace App\Model;


class SubmitterHasCuGroup extends Base {

	protected $tableName = 'submitter_has_cu_group';

	public function getAllByCuGroupId($id) {
		return $this->findAllBy(array('cu_group_id' => $id));
	}

	public function getAllByUserId($id){
		return $this->findAllBy(array('submitter_id' => $id));
	}


}