<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 26.3.2015
 * Time: 12:40
 */

namespace App\CrudComponents\Journal;


class JournalAddForm extends JournalForm {

	public function __construct(\App\Model\Journal $journalModel, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this['name']->addRule(function($nameField) use ($journalModel) {
			if($journalModel->findOneByName($nameField->getValue())){
				return false;
			} else return true;
		}, "Journal already exists.", $this);
	}

}
?>