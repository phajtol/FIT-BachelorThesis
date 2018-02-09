<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 5.3.2015
 * Time: 16:57
 */

namespace App\Model;


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
	function findByLogin($login){
		return $this->findOneBy(array(
			self::COLUMN_LOGIN	=>	$login
		));
	}

	public function associateToUser($user_id, $login, $password_hash)
	{
		return $this->createOrUpdate(array(
			self::COLUMN_USER_ID		=>	$user_id,
			self::COLUMN_PASSWORD_HASH	=>	$password_hash,
			self::COLUMN_LOGIN			=>	$login
		));
	}

	public function findOneByUserId($userId) {
		return $this->findOneBy(array('submitter_id' => $userId));
	}

	public function findAllByUserId($userId) {
		return $this->findAllBy(array('submitter_id' => $userId));
	}

}
