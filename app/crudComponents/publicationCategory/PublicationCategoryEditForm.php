<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 28.3.2015
 * Time: 17:40
 */

namespace App\CrudComponents\PublicationCategory;


class PublicationCategoryEditForm extends PublicationCategoryForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addHidden('id');
	}

}