<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 18.3.2015
 * Time: 20:07
 */

namespace App\CrudComponents\Author;


abstract class AuthorForm extends \App\Forms\BaseForm {

	public function __construct(\App\Model\Submitter $submitter,\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
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