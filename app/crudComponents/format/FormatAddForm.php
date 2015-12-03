<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 15.4.2015
 * Time: 16:44
 */

namespace App\CrudComponents\Format;


class FormatAddForm extends FormatForm  {

	public function __construct(\App\Model\Format $formatModel, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this['name']->addRule(function($nameField) use ($formatModel) {
			if($formatModel->findOneByName($nameField->getValue())){
				return false;
			} else return true;
		}, "Format with such name already exists.", $this);
	}


}