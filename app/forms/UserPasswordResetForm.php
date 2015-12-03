<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 1.5.2015
 * Time: 15:41
 */

namespace App\Forms;


class UserPasswordResetForm extends UserPasswordForm {

	public function __construct(\App\Services\PasswordResetter $passwordResetter, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addHidden('retrieve_uid')->setRequired(true)->addRule(function($field) use($passwordResetter) {
			return $passwordResetter->checkRetrieveHashIsValid($field->value);
		}, 'Invalid retrieve hash was set. Please, retry the whole procedure or contact the site administrator.');

		$this->addSubmit('send', 'Change password');

	}


}