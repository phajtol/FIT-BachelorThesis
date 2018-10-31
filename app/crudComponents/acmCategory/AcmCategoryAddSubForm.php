<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 31.3.2015
 * Time: 16:44
 */

namespace App\CrudComponents\AcmCategory;


class AcmCategoryAddSubForm extends AcmCategoryAddForm {

	public function __construct(
		\App\Model\AcmCategory $acmCategoryModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {

		parent::__construct($acmCategoryModel, $parent, $name);

		// parent category id
		$this->addHidden('parent_id')
			->addRule(\Nette\Application\UI\Form::INTEGER)
            ->setRequired(true);
	}

}