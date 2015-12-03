<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 1.5.2015
 * Time: 14:48
 */

namespace App\Services;


use Nette\Mail\Message;

class PasswordResetter extends \Nette\Object {

	/**
	 * @var \Nette\Mail\IMailer
	 */
	protected $mailer;

	/**
	 * @var \App\Model\Retrieve
	 */
	protected $retrieveModel;


	/** @var  \App\Services\Authenticators\LoginPassAuthenticator */
	protected $loginPassAuthenticator;

	/** @var  \App\Model\Submitter */
	protected $submitterModel;

	protected $fromEmailAddress;
	protected $adminEmailAddress;
	protected $siteUrl;

	/** @var  Callable */
	protected $callbackGetResetLink = null;

	function __construct($adminEmailAddress, $siteUrl, $fromEmailAddress,
	                     \App\Services\Authenticators\LoginPassAuthenticator $loginPassAuthenticator, \Nette\Mail\IMailer $mailer,  \App\Model\Retrieve $retrieveModel, \App\Model\Submitter $submitterModel) {
		$this->loginPassAuthenticator = $loginPassAuthenticator;
		$this->mailer = $mailer;
		$this->retrieveModel = $retrieveModel;
		$this->submitterModel = $submitterModel;

		$this->siteUrl = $siteUrl;
		$this->adminEmailAddress = $adminEmailAddress;
		$this->fromEmailAddress = $fromEmailAddress;
	}

	/**
	 * Sets the password retrieve url generator
	 * @param callable $callback function that returns a password retrieval url based on retrieve hash
	 * ex.: func($hash){ return "http://..." . $hash; }
	 */
	public function setCallbackGetResetLink(Callable $callback) {
		$this->callbackGetResetLink = $callback;
	}

	/**
	 * Resets user password and sends him an email with that new password.
	 * @param $user Object user object
	 * @param string $newPassword if not set, new password will be generated
	 */
	public function resetPassword($user, $newPassword = ''){

		if(!$newPassword) $newPassword = \Nette\Utils\Random::generate(10);

		$this->loginPassAuthenticator->associateLoginPasswordToUser($user->id, $user->nickname, $newPassword);

		$message = $this->prepareResetEmail($user, $user->nickname, $newPassword);

		$this->mailer->send($message);
	}

	/**
	 * Performs new retrieval link creation. An email with password retrieval link is sent to the user.
	 * WARNING! setCallbackGetResetLink must be called before calling this method.
	 * @param $user Object user object
	 */
	public function createPasswordRetrieve($user) {

		$retrieveHash = \Nette\Utils\Random::generate(32, '0-9a-zA-Z');

		$this->retrieveModel->deleteByUserId($user->id);

		$message = $this->prepareResetLinkEmail($user, $retrieveHash);

		$this->retrieveModel->insert(array('submitter_id' => $user->id, 'uid_hash' => $retrieveHash));

		$this->mailer->send($message);
	}

	public function checkRetrieveHashIsValid($retrieveHash) {
		if($this->retrieveModel->findOneBy(array('uid_hash' => $retrieveHash))) return true; else return false;
	}

	public function performPasswordChange($retrieveHash, $newPassword) {
		$retrieve = $this->retrieveModel->findOneBy(array('uid_hash' => $retrieveHash));

		if(!$retrieve) throw new \Nette\InvalidArgumentException('The retrieve hash is not valid');

		$user = $this->submitterModel->find($retrieve->submitter_id);
		if(!$user) throw new \Nette\InvalidStateException('The user given in retrieve doesnt exist');

		$this->loginPassAuthenticator->associateLoginPasswordToUser($user->id, $user->nickname, $newPassword);

		$retrieve->delete();

		return $user->nickname;
	}

	protected function prepareResetEmail($user, $login, $newPassword) {
		$mail = new Message;
		$mail->addTo($user->email)
			->setFrom($this->fromEmailAddress)
			->setSubject('New Password into Database of Publications and Conferences')
			->setHTMLBody("
                    Dear $user->name $user->surname,
                    <br /><br />
                    this message was automatically sent as a reaction on your request for password retrieval. Your new login information is:
                    <br /><br />
                    login: $login <br />
                    password: $newPassword <br />
                    url: <a href=\"$this->siteUrl\" target=\"_blank\">$this->siteUrl</a>
                    <br /><br />
                    With other questions or suggestions please contact administrator on $this->adminEmailAddress
                    <br /><br />
                    Have a nice day
                    <br />
                    ");
		return $mail;
	}

	protected function prepareResetLinkEmail($user, $hash) {
		if(!$this->callbackGetResetLink) throw new \Nette\InvalidStateException('Callback for generating password reset link has not been defined. It must be set via setter in given presenter.');

		$cb = $this->callbackGetResetLink;
		$resetLink = $cb($hash);

		$mail = new Message;
		$mail->addTo($user->email)
			->setFrom($this->fromEmailAddress)
			->setSubject('Password reset link into Database of Publications and Conferences')
			->setHTMLBody("
                    Dear $user->name $user->surname,
                    <br /><br />
                    this message was automatically sent as a reaction on your request for password retrieval. Link for reseting password is:
                    <br /><br />
                    url: <a href=\"$resetLink\" target=\"_blank\">$resetLink</a>
                    <br /><br />
                    With other questions or suggestions please contact site administrator on $this->adminEmailAddress
                    <br /><br />
                    Have a nice day
                    <br />
                    ");
		return $mail;
	}

}