<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 28.3.2015
 * Time: 17:36
 */

namespace App\CrudComponents\PublicationCategory;


class PublicationCategoryAddForm extends PublicationCategoryForm {

	public function __construct(
		\App\Model\Categories $publicationCategoryModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {

		parent::__construct($parent, $name);

		$this['name']->addRule(function($nameField, $form) use ($publicationCategoryModel) {
			if($publicationCategoryModel->findOneByName($nameField->value)){
				return false;
			} else return true;
		}, "Record with such name already exists.", $this);

	}

}