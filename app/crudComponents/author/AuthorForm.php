<?php

namespace App\CrudComponents\Author;


abstract class AuthorForm extends \App\Forms\BaseForm {

    /**
     * AuthorForm constructor.
     * @param \App\Model\Submitter $submitter
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\App\Model\Submitter $submitter,\Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->addText('name', 'Name')
            ->addRule($this::MAX_LENGTH, 'Name is way too long', 50)
            ->setRequired('Name is required.');

		$this->addText('middlename', 'Middlename')
            ->addRule($this::MAX_LENGTH, 'Middlename is way too long', 50)
            ->setRequired(false);

		$this->addText('surname', 'Surname')
            ->addRule($this::MAX_LENGTH, 'Surname is way too long', 50)
            ->setRequired('Surname is required.');

		$this->addSelect('user_id', 'User', $submitter->getPairs())->setPrompt("-- none --");

		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');

		$this->setModal(true);
		$this->setAjax(true);
	}

}