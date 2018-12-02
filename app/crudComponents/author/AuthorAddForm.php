<?php

namespace App\CrudComponents\Author;


class AuthorAddForm extends AuthorForm {


    /**
     * AuthorAddForm constructor.
     * @param \App\Model\Submitter $submitterModel
     * @param \App\Model\Author $authorModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\App\Model\Submitter $submitterModel,
                                \App\Model\Author $authorModel,
                                \Nette\ComponentModel\IContainer $parent = NULL,
                                string $name = NULL)
    {
		parent::__construct($submitterModel,$parent, $name);

		$this['surname']->addRule(function ($name, $form) use ($authorModel) {
			if($authorModel->findOneByName($form['name']->value, $form['middlename']->value, $form['surname']->value)){
				return false;
			} else return true;
		}, "Author witch such name already exists.", $this);

		$this->addConfirmableRule('surname', function () use ($authorModel) {
			$record = $authorModel->getAuthorNameByAuthorName($this['name']->value, $this['middlename']->value, $this['surname']->value);

			if ($record) {
				return false;
			}

			return true;

		}, 'WARNING: Similar author has been found.');

	}

}