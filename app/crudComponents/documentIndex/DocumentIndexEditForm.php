<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 15.4.2015
 * Time: 16:44
 */

namespace App\CrudComponents\DocumentIndex;


class DocumentIndexEditForm extends DocumentIndexForm {


	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addHidden('id');
	}

}