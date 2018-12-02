<?php

namespace App\CrudComponents\Publisher;


abstract class PublisherForm extends \App\Forms\BaseForm {

    /**
     * PublisherForm constructor.
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
	{
		parent::__construct($parent, $name);

		$this->setModal(true);
		$this->setAjax(true);

		$this->addText('name', 'Name')
			->addRule($this::MAX_LENGTH, 'Name is way too long', 500)
			->setRequired('Name is required.');

		$this->addText('address', 'Address')
            ->addRule($this::MAX_LENGTH, 'Address is way too long', 500)
            ->setRequired(false);

		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');
	}

}