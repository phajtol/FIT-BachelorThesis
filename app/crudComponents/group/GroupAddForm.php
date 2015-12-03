<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.5.2015
 * Time: 22:46
 */

namespace App\CrudComponents\Group;


class GroupAddForm extends GroupForm {


	public function __construct(\App\Model\Group $groupModel,  \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this['name']->addRule(function($i) use ($groupModel) {
			if($groupModel->findOneByName($i->value)) return false; else return true;
		}, "Name already exists.", $parent);
	}


}