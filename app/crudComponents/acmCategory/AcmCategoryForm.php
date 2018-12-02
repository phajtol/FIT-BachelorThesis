<?php

namespace App\CrudComponents\AcmCategory;


abstract class AcmCategoryForm extends \App\Forms\BaseForm {

    /**
     * AcmCategoryForm constructor.
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
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