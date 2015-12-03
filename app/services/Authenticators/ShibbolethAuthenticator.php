<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 6.5.2015
 * Time: 16:35
 */

namespace App\Services\Authenticators;


use App\Helpers\Func;
use Nette\Security\AuthenticationException;

class ShibbolethAuthenticator implements \Nette\Security\IAuthenticator {

	/** @var  \App\Model\GlobalParams */
	protected $globalParams;

	/** @var  \App\Model\ShibbolethParams */
	protected $shibbolethParams;

	/** @var  BaseAuthenticator */
	protected $baseAuthenticator;

	/** @var  \App\Model\AuthShibboleth */
	protected $authShibbolethModel;

	/** @var  \App\Model\Submitter */
	protected $submitterModel;

	/** @var  \App\Services\IdentityInitializer */
	protected $identityInitializer;

	function __construct(\App\Model\AuthShibboleth $authShibbolethModel, BaseAuthenticator $baseAuthenticator, \App\Model\GlobalParams $globalParams, \App\Model\ShibbolethParams $shibbolethParams, \App\Model\Submitter $submitterModel, \App\Services\IdentityInitializer $identityInitializer) {
		$this->authShibbolethModel = $authShibbolethModel;
		$this->baseAuthenticator = $baseAuthenticator;
		$this->globalParams = $globalParams;
		$this->shibbolethParams = $shibbolethParams;
		$this->submitterModel = $submitterModel;
		$this->identityInitializer = $identityInitializer;
	}


	/**
	 * Performs an authentication
	 * and returns IIdentity on success or throws AuthenticationException
	 * @return \Nette\Security\IIdentity
	 * @throws AuthenticationException
	 */
	function authenticate(array $credentials) {
		$credentials = $credentials[0];

		if(!isset($credentials['mail']) || !isset($credentials['REMOTE_USER'])) {
			throw new AuthenticationException(sprintf('Cannot obtain critical information from IdP. Please contact the administrator on %s', $this->globalParams->getAdminEmailAddress()));
		} else {

			// retrieve user email
			$email = $credentials['mail'];
			$userName = $credentials['REMOTE_USER'];

			// retrieve user name
			$name = Func::getValOrNull($credentials, 'givenName');
			$surname = Func::getValOrNull($credentials, 'sn');
			if(!$name && !$surname && ($fullName = Func::getValOrNull($credentials, 'cn'))) {
				$fullNameExp = explode(' ', $fullName);
				$name = $fullNameExp[0];
				$surname = $fullNameExp[count($fullNameExp)-1];
			}
			$hisGroups = Func::getValOrNull($credentials, 'isMemberOf');


			// check user is not already in the system
			if(!($shibbolethAuthRecord = $this->authShibbolethModel->findByUsernameAndEmail($userName, $email))) {
				// user does not exist yet

				// has an account with different auth method?
				if($userId = $this->submitterModel->findOneByEmail($email)) {

					// he is a fraud:-)
					throw new ShibbolethAuthenticationException(sprintf('User associated with email %s uses a different authentication system (%s). Please contact the system administrator (%s).',
						$email, $this->baseAuthenticator->translateAuthMethod($this->baseAuthenticator->getUserAuthenticationMethod($userId)), $this->globalParams->getAdminEmailAddress()));

				} else {

					// he isn't really in the system

					$hisRoles = null;

					if($hisGroups) { // 'isMemberOf' field defined by shibboleth
						if ($groupRoles = $this->shibbolethParams->getGroupRoles()) {    // group => roles defined in config
							foreach ($groupRoles as $group => $roles) {
								if(strpos($hisGroups, 'cn=' . $group) !== false) {
									if(is_null($hisRoles)) $hisRoles = array();
									$hisRoles = array_merge($hisRoles, $roles);
								}
							}
						}
					}

					if(is_null($hisRoles)) {
						// user doesn't belong to any of the defined groups or that groups have not been configured
						// or no 'isMemberOf' info received from shibboleth

						$hisRoles = $this->shibbolethParams->getDefaultRoles();
						if (!$hisRoles) $hisRoles = [];
					}

					if(empty($hisRoles)) {
						throw new ShibbolethAuthenticationException(sprintf('Your have no permissions within your group fetched from shibboleth. Please, contact system administrator at %s.', $this->globalParams->getAdminEmailAddress()));
					}

					$nickname = $this->submitterModel->getNearestFreeNickname($userName);

					$userId = $this->baseAuthenticator->createNewUser($nickname, $hisRoles, $email, $name, $surname);

					$this->authShibbolethModel->associateToSubmitter($userId, $userName, $email);

				}

			} else {
				// user is already in the system
				$userIdCol = \App\Model\AuthShibboleth::COLUMN_SUBMITTER_ID;
				$userId = $shibbolethAuthRecord->$userIdCol;    unset($userIdCol);

				// update empty fields
				$upd = array();
				$user = $this->submitterModel->find($userId);
				if(!$user->name && $name) $upd['name'] = $name;
				if(!$user->surname && $surname) $upd['surname'] = $name;
				$upd['id'] = $user->id;
				$this->submitterModel->update($upd);
			}

        }

		$identity = new \Nette\Security\Identity($userId);

		$this->identityInitializer->initializeIdentity($identity);

		return $identity;
	}




}
?>