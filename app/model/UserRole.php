<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;


class UserRole extends Base {

    /** @var string */
	protected $tableName = 'user_role';

	/** @var string */
	protected $userIdColumn = 'user_id';

    /**
     * @param int $userId
     * @return Selection
     */
	public function findAllByUserId(int $userId): Selection
	{
		return $this->findAllBy(
			array($this->userIdColumn => $userId)
		);
	}

    /**
     * @param $user_id
     * @param $role
     * @return \Nette\Database\Table\ActiveRow
     */
	public function attachRoleToUser(int $user_id, string $role): ActiveRow
    {
		return $this->insert(array(
			"user_id"		=>	$user_id,
			"role"			=>	$role
		));
	}

    /**
     * @param $user_id
     * @return int
     */
	public function clearAllUserRoles(int $user_id): int
    {
		return $this->findAllBy(array("user_id" => $user_id))->delete();
	}

    /**
     * @param int $user_id
     * @param array $roles
     */
	public function attachRolesToUser(int $user_id, array $roles): void
    {
		$role_insertion = array();
		foreach ($roles as $role) {
		    $role_insertion[] = array('user_id' => $user_id, 'role' => $role);
        }
		$this->insertMulti($role_insertion);
	}

    /**
     * @param int $user_id
     * @param array $roles
     */
	public function setUserRoles(int $user_id, array $roles): void
    {
		if (empty($roles)) {
		    throw new \Nette\InvalidArgumentException('Role list cannot be empty');
        }

		$this->findAllByUserId($user_id)->delete();
		$this->attachRolesToUser($user_id, $roles);
	}

}
