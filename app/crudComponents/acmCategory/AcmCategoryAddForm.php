<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 31.3.2015
 * Time: 16:42
 */

namespace App\CrudComponents\AcmCategory;


class AcmCategoryAddForm extends AcmCategoryForm {

	public function __construct(
		\App\Model\AcmCategory $acmCategoryModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {

		parent::__construct($parent, $name);

		$this['name']->addRule(function($nameField, $form) use ($acmCategoryModel) {
			if($acmCategoryModel->findOneByName($nameField->value)){
				return false;
			} else return true;
		}, "Record with such name already exists.", $this);

	}

}