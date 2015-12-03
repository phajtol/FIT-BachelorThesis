<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 10.3.2015
 * Time: 0:15
 */

namespace App\Model;


class UserRole extends Base {

	protected $tableName = 'user_role';

	protected $userIdColumn = 'user_id';

	public function findAllByUserId($userId)
	{
		return $this->findAllBy(
			array($this->userIdColumn => $userId)
		);
	}

	public function attachRoleToUser($user_id, $role) {
		return $this->insert(array(
			"user_id"		=>	$user_id,
			"role"			=>	$role
		));
	}

	public function clearAllUserRoles($user_id) {
		return $this->findAllBy(array("user_id" => $user_id))->delete();
	}

	public function attachRolesToUser($user_id, $roles) {
		$role_insertion = array();
		foreach($roles as $role) $role_insertion[] = array('user_id' => $user_id, 'role' => $role);
		$this->insertMulti($role_insertion);
	}

	public function setUserRoles($user_id, $roles) {
		if(empty($roles)) throw new \Nette\InvalidArgumentException('Role list cannot be empty');
		$this->findAllByUserId($user_id)->delete();
		$this->attachRolesToUser($user_id, $roles);
	}

}
?>