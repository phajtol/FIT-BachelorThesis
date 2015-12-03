<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 2.4.2015
 * Time: 22:12
 */

namespace App\CrudComponents\ConferenceCategory;


class ConferenceCategoryEditForm extends ConferenceCategoryForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addHidden('id');
	}

}