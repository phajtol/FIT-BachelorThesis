<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 26.3.2015
 * Time: 20:22
 */

namespace App\CrudComponents\Conference;


class ConferenceEditForm extends ConferenceForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addHidden('id');
	}


}