<?php

namespace App\CrudComponents\AcmCategory;


class AcmCategoryAddSubForm extends AcmCategoryAddForm {

    /**
     * AcmCategoryAddSubForm constructor.
     * @param \App\Model\AcmCategory $acmCategoryModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
		\App\Model\AcmCategory $acmCategoryModel,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL) {

		parent::__construct($acmCategoryModel, $parent, $name);

		// parent category id
		$this->addHidden('parent_id')
			->addRule(\Nette\Application\UI\Form::INTEGER)
            ->setRequired(true);
	}

}