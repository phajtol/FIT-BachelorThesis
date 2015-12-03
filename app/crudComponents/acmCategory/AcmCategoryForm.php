<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 31.3.2015
 * Time: 16:42
 */

namespace App\CrudComponents\AcmCategory;


abstract class AcmCategoryForm extends \App\Forms\BaseForm {

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