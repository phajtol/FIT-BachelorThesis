<?php

namespace App\CrudComponents\Attribute;


class AttributeAddForm extends AttributeForm {

    /**
     * AttributeAddForm constructor.
     * @param \App\Model\Attribute $attributeModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\App\Model\Attribute $attributeModel, \Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this['name']->addRule(function ($nameField) use ($attributeModel) {
			if ($attributeModel->findOneByName($nameField->getValue())) {
				return false;
			} else return true;
		}, "Attribute with such name already exists.", $this);
	}

}