<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.4.2015
 * Time: 2:50
 */

namespace App\CrudComponents\CuGroup;


class CuGroupEditForm extends CuGroupForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addHidden('id');
	}

}