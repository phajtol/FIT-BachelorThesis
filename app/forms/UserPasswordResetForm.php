<?php

namespace App\Forms;


class UserPasswordResetForm extends UserPasswordForm {

    /**
     * UserPasswordResetForm constructor.
     * @param \App\Services\PasswordResetter $passwordResetter
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string $name
     */
	public function __construct(\App\Services\PasswordResetter $passwordResetter, \Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->addHidden('retrieve_uid')
            ->setRequired(true)
            ->addRule(function ($field) use ($passwordResetter) {
			    return $passwordResetter->checkRetrieveHashIsValid($field->value);
		    }, 'Invalid retrieve hash was set. Please, retry the whole procedure or contact the site administrator.');

		$this->addSubmit('send', 'Change password');

	}

}