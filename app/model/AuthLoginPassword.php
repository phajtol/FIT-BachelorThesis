<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;


class AuthLoginPassword extends Base {

	/**
	 * Name of the database table
	 * @var string
	 */
	protected $tableName = 'auth_login_password';

	const COLUMN_LOGIN = 'login';
	const COLUMN_PASSWORD_HASH = 'password';
	const COLUMN_USER_ID = 'submitter_id';

	/**
	 * @param $login
	 * @return FALSE|\Nette\Database\Table\ActiveRow
	 */
	function findByLogin(string $login)
    {
		return $this->findOneBy(array(
			self::COLUMN_LOGIN	=>	$login
		));
	}

    /**
     * @param int $user_id
     * @param string $login
     * @param string $password_hash
     * @return ActiveRow
     */
	public function associateToUser(int $user_id, string $login, string $password_hash): ActiveRow
	{
		return $this->createOrUpdate(array(
			self::COLUMN_USER_ID		=>	$user_id,
			self::COLUMN_PASSWORD_HASH	=>	$password_hash,
			self::COLUMN_LOGIN			=>	$login
		));
	}

    /**
     * @param int $userId
     * @return FALSE|ActiveRow
     */
	public function findOneByUserId(int $userId)
    {
		return $this->findOneBy(array('submitter_id' => $userId));
	}

    /**
     * @param int $userId
     * @return \Nette\Database\Table\Selection
     */
	public function findAllByUserId(int $userId): Selection
    {
		return $this->findAllBy(array('submitter_id' => $userId));
	}
}
