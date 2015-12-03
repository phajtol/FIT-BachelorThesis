<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 27.3.2015
 * Time: 23:09
 */

namespace App\CrudComponents\Attribute;


class AttributeEditForm extends AttributeForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addHidden('id');
	}

}