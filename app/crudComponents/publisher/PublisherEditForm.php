<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 19.3.2015
 * Time: 1:05
 */

namespace App\CrudComponents\Publisher;


class PublisherEditForm extends PublisherForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addHidden('id');
	}


}