<?php

namespace App\CrudComponents\CuGroup;


abstract class CuGroupForm extends \App\Forms\BaseForm {

    /**
     * CuGroupForm constructor.
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
        parent::__construct();
        if ($parent) {
            $parent->addComponent($this, $name);
        }

		$this->addText('name', 'Name')
            ->setRequired('Name is required.');

		$this->addText('conference_categories', 'Conference categories')
			->addRule(\PublicationFormRules::CATEGORIES, "Invalid category list")
            ->setRequired('Categories are required.');

		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');

		$this->setModal(true);
		$this->setAjax(true);
	}

}