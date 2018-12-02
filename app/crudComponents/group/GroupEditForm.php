<?php

namespace App\CrudComponents\Group;


class GroupEditForm extends GroupForm {

    /**
     * GroupEditForm constructor.
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string $name
     */
	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->addHidden('id');
	}

}