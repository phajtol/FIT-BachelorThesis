<?php

namespace App\CrudComponents\CuGroup;


class CuGroupAddForm extends CuGroupForm {

    /**
     * CuGroupAddForm constructor.
     * @param \App\Model\CuGroup $cuGroupModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\App\Model\CuGroup $cuGroupModel, \Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this['name']->addRule(function ($nameField) use ($cuGroupModel) {
			if ($cuGroupModel->findOneByName($nameField->getValue())){
				return false;
			} else {
			    return true;
            }
		}, "Conference user group with such name already exists.", $this)
        ->setRequired('Name is required.');
	}

}