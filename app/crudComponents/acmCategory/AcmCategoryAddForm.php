<?php

namespace App\CrudComponents\AcmCategory;


class AcmCategoryAddForm extends AcmCategoryForm {


    /**
     * AcmCategoryAddForm constructor.
     * @param \App\Model\AcmCategory $acmCategoryModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
		\App\Model\AcmCategory $acmCategoryModel,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this['name']->addRule(function ($nameField, $form) use ($acmCategoryModel) {
			if ($acmCategoryModel->findOneByName($nameField->value)) {
				return false;
			} else return true;
		}, "Record with such name already exists.", $this);
	}

}
