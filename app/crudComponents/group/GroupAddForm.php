<?php

namespace App\CrudComponents\Group;


class GroupAddForm extends GroupForm {

    /**
     * GroupAddForm constructor.
     * @param \App\Model\Group $groupModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\App\Model\Group $groupModel, \Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this['name']->addRule(function ($i) use ($groupModel) {
			if ($groupModel->findOneByName($i->value)) {
			    return false;
            } else {
			    return true;
            }
		}, "Name already exists.", $parent);
	}

}