<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 2.4.2015
 * Time: 22:12
 */

namespace App\CrudComponents\ConferenceCategory;


class ConferenceCategoryAddSubForm extends ConferenceCategoryAddForm {

	public function __construct(
		\App\Model\ConferenceCategory $conferenceCategoryModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {

		parent::__construct($conferenceCategoryModel, $parent, $name);

		// parent category id
		$this->addHidden('parent_id')
			->addRule(\Nette\Application\UI\Form::INTEGER)
            ->setRequired(true);
	}

}