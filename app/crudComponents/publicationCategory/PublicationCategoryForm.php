<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 28.3.2015
 * Time: 17:34
 */

namespace App\CrudComponents\PublicationCategory;


use App\Forms\BaseForm;

class PublicationCategoryForm extends BaseForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {

		parent::__construct($parent, $name);

		$this->addText('name', 'Name')
			->addRule($this::MAX_LENGTH, 'Name is way too long', 50)
			->setRequired('Name is required.');

		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');

		$this->setModal(true);
		$this->setAjax(true);

	}


}