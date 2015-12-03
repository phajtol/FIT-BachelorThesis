<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 6.5.2015
 * Time: 22:46
 */

namespace App\Services;


class IdentityInitializer {

	/** @var  \App\Model\Submitter */
	protected $submitterModel;

	/** @var  \App\Model\UserRole */
	protected $userRoleModel;

	function __construct(\App\Model\Submitter $submitterModel, \App\Model\UserRole $userRoleModel) {
		$this->submitterModel = $submitterModel;
		$this->userRoleModel = $userRoleModel;
	}


	public function initializeIdentity(\Nette\Security\Identity &$identity){
		$submitter = $this->submitterModel->findOneById($identity->getId());
		if($submitter && $identity) {

			// load data
			$identity->name = $submitter->name;
			$identity->surname = $submitter->surname;
			$identity->email = $submitter->email;
			$identity->nickname = $submitter->nickname;

			// load roles
			$attached_roles_res = $this->userRoleModel->findAllByUserId($identity->getId());
			$roles = array();
			foreach($attached_roles_res as $attached_role) {
				$roles[] = $attached_role->role;
			}
			$identity->setRoles($roles);
		}

		return $identity;
	}

}