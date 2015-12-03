<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 27.3.2015
 * Time: 23:07
 */

namespace App\CrudComponents\Attribute;


class AttributeAddForm extends AttributeForm {

	public function __construct(\App\Model\Attribute $attributeModel, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this['name']->addRule(function($nameField) use ($attributeModel) {
			if($attributeModel->findOneByName($nameField->getValue())){
				return false;
			} else return true;
		}, "Attribute with such name already exists.", $this);
	}

}