<?php

namespace App\CrudComponents\Group;


class GroupForm extends \App\Forms\BaseForm {

    /**
     * GroupForm constructor.
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
        parent::__construct($parent, $name);

		$this->addText('name', 'Name')
            ->addRule($this::MAX_LENGTH, 'Name is way too long', 250)
            ->setRequired('Name is required.');

		$this->addSubmit('send', 'Done');

		$this->setAjax(true);
		$this->setModal(true);
	}

}