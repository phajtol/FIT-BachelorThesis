<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 6.5.2015
 * Time: 16:31
 */

namespace App\Model;


class AuthShibboleth extends Base {

	protected $tableName = 'auth_shibboleth';

	const COLUMN_USERNAME = 'username';
	const COLUMN_EMAIL = 'email';
	const COLUMN_SUBMITTER_ID = 'submitter_id';

	/**
	 * @param $login
	 * @return FALSE|\Nette\Database\Table\ActiveRow
	 */
	function findByUsernameAndEmail($userName, $email){
		return $this->findOneBy(array(
			self::COLUMN_USERNAME   =>	$userName,
			self::COLUMN_EMAIL      =>  $email
		));
	}

	function associateToSubmitter($submitterId, $userName, $email){
		return $this->createOrUpdate(array(
			self::COLUMN_SUBMITTER_ID 	=>	$submitterId,
			self::COLUMN_USERNAME		=>	$userName,
			self::COLUMN_EMAIL          =>  $email
		));
	}

	function deleteFromSubmitter($userId) {
		return $this->findAllBy(array( self::COLUMN_SUBMITTER_ID  => $userId))->delete();
	}

	function findOneByUserId($userId) {
		return $this->findOneBy(array( self::COLUMN_SUBMITTER_ID  => $userId));
	}


}