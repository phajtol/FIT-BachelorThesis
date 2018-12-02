<?php

namespace App\CrudComponents\ConferenceCategory;


class ConferenceCategoryAddSubForm extends ConferenceCategoryAddForm {

	public function __construct(
		\App\Model\ConferenceCategory $conferenceCategoryModel,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
		parent::__construct($conferenceCategoryModel, $parent, $name);

		// parent category id
		$this->addHidden('parent_id')
			->addRule(\Nette\Application\UI\Form::INTEGER)
            ->setRequired(true);
	}

}