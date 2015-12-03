<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.4.2015
 * Time: 22:49
 */

namespace App\CrudComponents\User;


class UserAddForm extends UserForm {

	public function __construct(\Nette\Security\User $loggedUser, $availableRoles, $availableCuGroups, $availableAuthTypes,
								\App\Model\Submitter $submitterModel,
								\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {

		parent::__construct($loggedUser, $availableRoles, $availableCuGroups, $availableAuthTypes, $parent, $name);

		$this['email']->addRule(function($field) use($submitterModel) {
			if($field->value != '') {
				if($submitterModel->findOneByEmail($field->value)) return false;
			}
			return true;
		}, 'User with such email already exists.');

		// todo really?
		$this['nickname']->addRule(function($field) use($submitterModel) {
			if($field->value != '') {
				if($submitterModel->findOneByNickname($field->value)) return false;
			}
			return true;
		}, 'User with such nickname already exists.');

	}


}