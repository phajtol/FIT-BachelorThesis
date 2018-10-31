<?php

namespace App\Services\Authenticators;

use Nette;
use Nette\Utils\Strings;


/**
 * Users management.
 */
class LoginPassAuthenticator implements Nette\Security\IAuthenticator
{

    use Nette\SmartObject;

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
		} elseif (!\password_verify($password, $row->password)) {
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
  public function associateLoginPasswordToUser($user_id, $login, $password) {
    $this->authLoginPasswordModel->associateToUser(
      $user_id,
      $login,
      \password_hash($password, PASSWORD_BCRYPT)
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
