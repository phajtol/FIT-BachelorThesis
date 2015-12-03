<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 1.5.2015
 * Time: 15:38
 */

namespace App\Forms;


class UserPasswordForm extends BaseForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addPassword('pass', 'New password')->addRule($this::MIN_LENGTH, 'New password is way too short (min 8 characters)', 8)->addRule($this::MAX_LENGTH, 'New password is way too long', 20)->setRequired('New Password is required.');
		$this->addPassword('pass_repetition', 'New Password repetition')->setRequired('New Password repetition is required.')->addRule($this::EQUAL, 'New Passwords does not match.', $this['pass']);

	}


}