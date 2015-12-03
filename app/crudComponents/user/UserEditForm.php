<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.4.2015
 * Time: 22:54
 */

namespace App\CrudComponents\User;


class UserEditForm extends UserForm {
	public function __construct(\Nette\Security\User $loggedUser, $availableRoles, $availableCuGroups, $availableAuthTypes,
	                            \App\Model\Submitter $submitterModel,
								\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($loggedUser, $availableRoles, $availableCuGroups, $availableAuthTypes, $parent, $name);

		$this->addHidden('id');


		$this['email']->addRule(function($field) use($submitterModel) {
			if($field->value != '') {
				$userFound = $submitterModel->findOneByEmail($field->value);
				if(!$userFound) return true;
				if($userFound) return $userFound->id == $this['id']->value;
			}
			return true;
		}, 'User with such email already exists.');

		// todo really?
		$this['nickname']->addRule(function($field) use($submitterModel) {
			if($field->value != '') {
				$userFound = $submitterModel->findOneByNickname($field->value);
				if(!$userFound) return true;
				if($userFound) return $userFound->id == $this['id']->value;
			}
			return true;
		}, 'User with such nickname already exists.');

	}


}