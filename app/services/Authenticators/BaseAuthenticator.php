<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 5.3.2015
 * Time: 16:56
 */

namespace App\Services\Authenticators;


use App\Interfaces\IAuthMethodTranslator;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\IIdentity;
use Nette\SmartObject;


class BaseAuthenticator implements  IAuthenticator, IAuthMethodTranslator {

    use SmartObject;

	const DEFAULT_ROLE = 'reader';

	const AUTH_LDAP = 'ldap';
	const AUTH_LOGIN_PASS = 'login_pass';
	const AUTH_SHIBBOLETH = 'shibboleth';

	/** @var LoginPassAuthenticator */
	protected $loginPassAuthenticator;

	/** @var \foglcz\LDAP\Authenticator */
	protected $ldapAuthenticator = null;

	/** @var \App\Model\AuthShibboleth */
	protected $authShibbolethModel;

	/** @var \App\Model\AuthLdap */
	protected $authLdapModel = null;

	/** @var \App\Model\Submitter */
	protected $submitterModel;

	/** @var \App\Model\UserRole */
	protected $userRoleModel;

	/** @var  \App\Model\UserSettings */
	protected $userSettingsModel;

	/** @var  \App\Model\GeneralSettings */
	protected $generalSettingsModel;

	/** @var  \App\Services\PasswordResetter */
	protected $passwordResetter;

	/** @var  \App\Services\IdentityInitializer */
	protected $identityInitializer;


    /**
     * BaseAuthenticator constructor.
     * @param LoginPassAuthenticator $loginPassAuthenticator
     * @param \App\Model\Submitter $submitterModel
     * @param \App\Model\UserRole $userRoleModel
     * @param \App\Model\UserSettings $userSettingsModel
     * @param \App\Model\GeneralSettings $generalSettingsModel
     * @param \App\Services\PasswordResetter $passwordResetter
     * @param \App\Model\AuthLdap $authLdapModel
     * @param \App\Model\AuthShibboleth $authShibbolethModel
     * @param \App\Services\IdentityInitializer $identityInitializer
     */
	function __construct(LoginPassAuthenticator $loginPassAuthenticator,
                         \App\Model\Submitter $submitterModel,
                         \App\Model\UserRole $userRoleModel,
                         \App\Model\UserSettings $userSettingsModel,
                         \App\Model\GeneralSettings $generalSettingsModel,
                         \App\Services\PasswordResetter $passwordResetter,
                         \App\Model\AuthLdap $authLdapModel,
                         \App\Model\AuthShibboleth $authShibbolethModel,
                         \App\Services\IdentityInitializer $identityInitializer)
	{
		$this->loginPassAuthenticator = $loginPassAuthenticator;
		$this->submitterModel = $submitterModel;
		$this->userRoleModel = $userRoleModel;
		$this->authLdapModel = $authLdapModel;
		$this->authShibbolethModel = $authShibbolethModel;

		$this->userSettingsModel = $userSettingsModel;
		$this->generalSettingsModel = $generalSettingsModel;

		$this->passwordResetter = $passwordResetter;
		$this->identityInitializer = $identityInitializer;
	}

	/**
	 * @param \foglcz\LDAP\Authenticator $ldapAuthenticator
	 */
	public function setupLdap(\foglcz\LDAP\Authenticator $ldapAuthenticator): void
	{
		if ($this->ldapAuthenticator !== null) {
		    return;
        }

		$this->ldapAuthenticator = $ldapAuthenticator;

		$this->ldapAuthenticator->addSuccessHandler("id", function (\Toyota\Component\Ldap\Core\Manager $ldap, $userData){
			$username = $userData['username'];
			$authLdapEntity = $this->authLdapModel->findByLogin($username);

			if ($authLdapEntity) {	// user has logged in via ldap
				return $authLdapEntity->submitter_id;
			} else {	// this is first login via given ldap username
				if(!isset($userData["userinfo"]) || empty($userData["userinfo"])) throw new \Exception("Cannot retrieve extended data from ldap");

				$submitter = $this->submitterModel->findOneBy(['nickname' => $username]);
				if($submitter) {	// user yet found by nickname
					$this->updateUserDetails($submitter->id, $userData["userinfo"]["email"], $userData["userinfo"]["name"], $userData["userinfo"]["surname"]);
					$this->authLdapModel->associateToSubmitter($submitter->id, $username);
					return $submitter->id;
				} else {	// user not found, let's create the new entity now
					if ($new_user_id = $this->createNewUser($username, self::DEFAULT_ROLE, $userData["userinfo"]["email"], $userData["userinfo"]["name"], $userData["userinfo"]["surname"])) {
						// 1==1 hack - nette database is buggy (it inserts a query and returns false)
						if (1 == 1 || $this->authLdapModel->associateToSubmitter($new_user_id, $username)) {
							return $new_user_id;
						} else  throw new \Exception("Cannot insert new LDAP user");
					} else throw new \Exception("Cannot insert new user");
				}
			}
		});
	}


	/**
	 * Performs an authentication against e.g. database.
	 * and returns IIdentity on success or throws AuthenticationException
	 * @param $credentials array credentials
	 * @return IIdentity
	 * @throws AuthenticationException
	 */
	function authenticate(array $credentials): IIdentity
    {
		list($login, $pass) = $credentials;

		if($this->loginPassAuthenticator->loginExists($login) !== false || $this->ldapAuthenticator === null) {
		    // login-pass credentials exists, let's try auth
			$identity = $this->loginPassAuthenticator->authenticate($credentials);
		} else {	 // login-pass credentials don't exist, let's try ldap
			$identity = $this->ldapAuthenticator->authenticate($credentials);
		}

		$this->identityInitializer->initializeIdentity($identity);

		return $identity;
	}

	/**
	 * @param $nickname
	 * @param $role string|string[]
	 * @param string $email
	 * @param string $name
	 * @param string $surname
	 * @return int new user id | false
	 */
	public function createNewUser($nickname, $role = self::DEFAULT_ROLE, $email = "", $name = "", $surname = "") {
		$row = $this->submitterModel->createNew(
			$nickname, $email, $name, $surname
		);

		if($row) {

			if($role !== NULL) {
				if(is_array($role)) {
					$this->userRoleModel->attachRolesToUser($row->id, $role);
				} else {
					$this->userRoleModel->attachRoleToUser(
						$row->id, $role
					);
				}
			}

			$generalSettings = $this->generalSettingsModel->findOneBy(array('id' => 1));
			if($generalSettings) {
				$this->userSettingsModel->insertExplicit(
					$row->id,
					$generalSettings->pagination,
					$generalSettings->deadline_notification_advance
				);
			}

			return $row->id;
		} else return false;
	}

	public function updateUserDetails($user_id, $email, $name, $surname) {
		return $this->submitterModel->update([
		    'id'		=>	$user_id,
            'email'		=>	$email,
            'name'		=>	$name,
            'surname'	=>	$surname
		]);
	}

	/**
	 * Get available authentication method for the given user
	 * @param int $userId
	 * @return null|string
	 */
	public function getUserAuthenticationMethod(int $userId): ?string
    {
		if ($this->authLdapModel->findOneByUserId($userId)) {
		    return self::AUTH_LDAP;
        }
		if ($this->authShibbolethModel->findOneByUserId($userId)) {
		    return self::AUTH_SHIBBOLETH;
        }
		if($this->loginPassAuthenticator->areCredentialsAvailableForUser($userId)) {
		    return self::AUTH_LOGIN_PASS;
        }
		return null;
	}

    /**
     * @param int $userId
     * @param string $authMethod
     * @return bool
     * @throws \Exception
     */
	public function setUserAuthenticationMethod(int $userId, string $authMethod): bool
    {
		if ($authMethod == $this->getUserAuthenticationMethod($userId)) {
		    return true;
        } // nothing to change

		$user = $this->submitterModel->findOneById($userId);

		switch ($authMethod) {
			case self::AUTH_LDAP:
				$this->loginPassAuthenticator->deleteUserCredentials($user->id);
				$this->authShibbolethModel->deleteFromSubmitter($user->id);
				$this->authLdapModel->associateToSubmitter($user->id, $user->nickname);
				return true;
				break;

			case self::AUTH_LOGIN_PASS:
				$this->authLdapModel->deleteFromSubmitter($user->id);
				$this->authShibbolethModel->deleteFromSubmitter($user->id);
				$this->passwordResetter->resetPassword($user, \Nette\Utils\Random::generate(10));
				return true;
				break;

			case self::AUTH_SHIBBOLETH:
				$this->authLdapModel->deleteFromSubmitter($user->id);
				$this->loginPassAuthenticator->deleteUserCredentials($user->id);
				$this->authShibbolethModel->associateToSubmitter($user->id, $user->nickname, $user->email);

				break;

			default:
				throw new \Exception("Invalid authentication method set.");
		}
	}

	/**
	 * @param \Nette\Database\Table\Selection $sel
	 * @return array[] array of results - array(user_id => auth_type)
	 */
	public function getUsersAuthenticationMethods(\Nette\Database\Table\Selection $sel) {
		$ret = array();
		foreach($sel as $user) {
			$auth_type = null;
			if(count($user->related('auth_ldap'))) $auth_type = self::AUTH_LDAP;
			if(count($user->related('auth_login_password'))) $auth_type = self::AUTH_LOGIN_PASS;
			if(count($user->related('auth_shibboleth'))) $auth_type = self::AUTH_SHIBBOLETH;
			$ret[$user->id]   =  $auth_type;
		}
		return $ret;
	}

    /**
     * @param string|null $authMethod
     * @return string|null
     */
	public function translateAuthMethod(?string $authMethod): ?string
    {
		$translation = [
			self::AUTH_LOGIN_PASS   =>    "Password",
			self::AUTH_LDAP         =>    "LDAP",
			self::AUTH_SHIBBOLETH   =>    "Shibboleth"
		];

		if (isset($translation[$authMethod])) {
		    return $translation[$authMethod];
        }
		return $authMethod;
	}

    /**
     * @return array
     */
	public function getAvailableAuthMethods(): array
    {
		return [
			self::AUTH_LOGIN_PASS	=>	$this->translateAuthMethod(BaseAuthenticator::AUTH_LOGIN_PASS),
			self::AUTH_SHIBBOLETH	=>	$this->translateAuthMethod(BaseAuthenticator::AUTH_SHIBBOLETH),
			self::AUTH_LDAP		    =>	$this->translateAuthMethod(BaseAuthenticator::AUTH_LDAP)
		];
	}

}