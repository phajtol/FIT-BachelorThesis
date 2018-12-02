<?php

namespace App\CrudComponents\ConferenceCategory;


class ConferenceCategoryEditForm extends ConferenceCategoryForm {

    /**
     * ConferenceCategoryEditForm constructor.
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->addHidden('id');
	}

}