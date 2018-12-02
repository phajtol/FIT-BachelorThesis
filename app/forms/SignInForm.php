<?php

use Nette\Application\UI;
use Nette\ComponentModel\IContainer;

class SignInForm extends UI\Form {

    public function __construct(IContainer $parent = NULL, string $name = NULL)
    {
        parent::__construct($parent, $name);

        $this->addText('username', 'Login')
            ->setRequired('Please enter your Login.')
            ->addRule($this::MAX_LENGTH, 'Login you\'ve entered is way too long.', 100);

            // This was used for validation against local database
            // ->addRule(PublicationFormRules::LOGIN_EXISTS_INV, "Login does not exist.", $parent);

        $this->addPassword('password', 'Password')
            ->setRequired('Please enter your Password.')
            ->addRule($this::MAX_LENGTH, 'Password  you\'ve is way too long.', 500);

            // This was used for validation against local database
            //->addRule(PublicationFormRules::PASS_EXISTS_INV, "Wrong Password.", $parent);

        $this->addCheckbox('remember', 'Keep me signed in');
        $this->addSubmit('send', 'Sign in');
    }

}
