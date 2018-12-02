<?php

namespace App\Model;


use Nette\Database\Table\ActiveRow;

class CuGroup extends Base {

    /** @var string */
	protected $tableName = 'cu_group';

    /**
     * @param string $name
     * @return FALSE|ActiveRow
     */
	public function findOneByName(string $name)
    {
		return $this->findOneBy(array('name' => $name));
	}
}
