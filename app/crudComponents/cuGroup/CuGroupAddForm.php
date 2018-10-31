<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.4.2015
 * Time: 2:49
 */

namespace App\CrudComponents\CuGroup;


class CuGroupAddForm extends CuGroupForm {

	public function __construct(\App\Model\CuGroup $cuGroupModel, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this['name']->addRule(function($nameField) use ($cuGroupModel) {
			if($cuGroupModel->findOneByName($nameField->getValue())){
				return false;
			} else return true;
		}, "Conference user group with such name already exists.", $this)
        ->setRequired('Name is required.');
	}

}