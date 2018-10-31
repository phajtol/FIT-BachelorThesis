<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 28.3.2015
 * Time: 17:40
 */

namespace App\CrudComponents\PublicationCategory;


class PublicationCategoryAddSubForm extends PublicationCategoryAddForm {

	public function __construct(
		\App\Model\Categories $publicationCategoryModel,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {

		parent::__construct($publicationCategoryModel, $parent, $name);

		// parent category id
		$this->addHidden('categories_id')
			->addRule(\Nette\Application\UI\Form::INTEGER)
            ->setRequired(false);
	}

}