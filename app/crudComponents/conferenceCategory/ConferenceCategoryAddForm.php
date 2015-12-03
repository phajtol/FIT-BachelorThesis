<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 2.4.2015
 * Time: 22:12
 */

namespace App\CrudComponents\ConferenceCategory;


class ConferenceCategoryAddForm extends ConferenceCategoryForm {

	public function __construct(
		\App\Model\ConferenceCategory $conferenceCategoryModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {

		parent::__construct($parent, $name);

		$this['name']->addRule(function($nameField, $form) use ($conferenceCategoryModel) {
			if($conferenceCategoryModel->findOneByName($nameField->value)){
				return false;
			} else return true;
		}, "Record with such name already exists.", $this);

	}

}