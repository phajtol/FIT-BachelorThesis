<?php

namespace App\CrudComponents\PublicationCategory;


class PublicationCategoryAddForm extends PublicationCategoryForm {

    /**
     * PublicationCategoryAddForm constructor.
     * @param \App\Model\Categories $publicationCategoryModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\App\Model\Categories $publicationCategoryModel, \Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this['name']->addRule(function ($nameField, $form) use ($publicationCategoryModel) {
			if($publicationCategoryModel->findOneByName($nameField->value)){
				return false;
			} else {
			    return true;
            }
		}, "Record with such name already exists.", $this);

	}

}