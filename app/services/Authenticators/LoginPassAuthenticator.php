<?php

namespace App\Services\Authenticators;

use Nette,
	Nette\Utils\Strings;


/**
 * Users management.
 */
class LoginPassAuthenticator extends Nette\Object implements Nette\Security\IAuthenticator
{

	/**
	 * @var \App\Model\AuthLoginPassword
	 */
	protected $authLoginPasswordModel;

	/**
	 * Success handlers to in-load additional data / postprocess before identity creation
	 *
	 * @var Callback[]
	 */
	public $onSuccess = array();


	function __construct(\App\Model\AuthLoginPassword $authLoginPasswordModel)
	{
		$this->authLoginPasswordModel = $authLoginPasswordModel;
	}

	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$password = self::removeCapsLock($password);

		$row = $this->authLoginPasswordModel->findByLogin($username);

		if (!$row) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
		} elseif ($row->password != $this->calculateHash($password, $row->salt)) {
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
		}

		$user_id = strval($row['submitter_id']);

		$user_data = array("id"	=> $user_id);
		$this->onSuccess($user_data);

		return new Nette\Security\Identity($user_id, null, $user_data);
	}

	public function loginExists($login)
	{
		$alp = $this->authLoginPasswordModel->findByLogin($login);
		if($alp === false) return false; else return true;
	}

	/**
	 * @param Callable $handler <void>function(&$userData)
	 */
	public function addUserDataHandler(Callable $handler)
	{
		$this->onSuccess[] = $handler;
	}


	/**
	 * Fixes caps lock accidentally turned on.
	 * @return string
	 */
	private static function removeCapsLock($password)
	{
		return $password === Strings::upper($password)
			? Strings::lower($password)
			: $password;
	}

	/**
	 * Computes salted password hash.
	 * @param  string
	 * @return string
	 */
	public static function calculateHash($password, $salt = NULL)
	{
		// perhaps caps lock is on
		/* if ($password === Strings::upper($password)) {
		  $password = Strings::lower($password);
		  }
		  $password = substr($password, 0, self::PASSWORD_MAX_LENGTH);
		  return crypt($password, $salt ? : '$2a$07$' . Strings::random(22)); */

		return crypt(md5($password), '$6$rounds=5000$' . $salt . '$');
	}

	public function associateLoginPasswordToUser($user_id, $login, $password) {
		$salt = Nette\Utils\Random::generate(32);

		$this->authLoginPasswordModel->associateToUser(
			$user_id,
			$login,
			$this->calculateHash($password, $salt),
			$salt
		);
	}

	public function generateRandomPassword($length = 8) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		return Nette\Utils\Random::generate($length, $characters);
	}

	public function areCredentialsAvailableForUser($userId) {
		if($this->authLoginPasswordModel->findOneByUserId($userId)) return true;
		else return false;
	}

	public function deleteUserCredentials($userId) {
		$this->authLoginPasswordModel->findAllByUserId($userId)->delete();
	}

}
