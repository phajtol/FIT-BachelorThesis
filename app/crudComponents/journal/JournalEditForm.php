<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 26.3.2015
 * Time: 12:58
 */

namespace App\CrudComponents\Journal;


class JournalEditForm extends JournalForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addHidden('id');
	}


}
?>