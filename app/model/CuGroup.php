<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.4.2015
 * Time: 2:15
 */

namespace App\Model;


class CuGroup extends Base {

	protected $tableName = 'cu_group';

	public function findOneByName($name) {
		return $this->findOneBy(array('name' => $name));
	}

}