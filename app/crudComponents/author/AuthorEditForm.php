<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 18.3.2015
 * Time: 20:18
 */

namespace App\CrudComponents\Author;


class AuthorEditForm  extends AuthorForm {
	public function __construct(\App\Model\Submitter $submitterModel,\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($submitterModel,$parent, $name);

		$this->addHidden('id');
	}


}