<?php

namespace App\CrudComponents\ConferenceCategory;


class ConferenceCategoryAddForm extends ConferenceCategoryForm {

    /**
     * ConferenceCategoryAddForm constructor.
     * @param \App\Model\ConferenceCategory $conferenceCategoryModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
		\App\Model\ConferenceCategory $conferenceCategoryModel,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this['name']->addRule(function ($nameField, $form) use ($conferenceCategoryModel) {
			if ($conferenceCategoryModel->findOneByName($nameField->value)) {
				return false;
			} else {
			    return true;
            }
		}, "Record with such name already exists.", $this);
	}

}