<?php

namespace App\CrudComponents\PublicationCategory;


class PublicationCategoryAddSubForm extends PublicationCategoryAddForm {

    /**
     * PublicationCategoryAddSubForm constructor.
     * @param \App\Model\Categories $publicationCategoryModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\App\Model\Categories $publicationCategoryModel, \Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($publicationCategoryModel, $parent, $name);

		// parent category id
		$this->addHidden('categories_id')
			->addRule(\Nette\Application\UI\Form::INTEGER)
            ->setRequired(false);
	}

}