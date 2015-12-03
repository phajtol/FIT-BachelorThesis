<?php

namespace App\Model;

/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 9.3.2015
 * Time: 18:31
 */

class AuthLdap Extends Base {

	/**
	 * Name of the database table
	 * @var string
	 */
	protected $tableName = 'auth_ldap';

	const COLUMN_LOGIN = 'login';
	const COLUMN_SUBMITTER_ID = 'submitter_id';

	/**
	 * @param $login
	 * @return FALSE|\Nette\Database\Table\ActiveRow
	 */
	function findByLogin($login){
		return $this->findOneBy(array(
			self::COLUMN_LOGIN	=>	$login
		));
	}

	function associateToSubmitter($submitter_id, $login){
		return $this->insert(array(
			self::COLUMN_SUBMITTER_ID 	=>	$submitter_id,
			self::COLUMN_LOGIN 			=>	$login
		));
	}

	function deleteFromSubmitter($userId) {
		return $this->findAllBy(array( self::COLUMN_SUBMITTER_ID  => $userId))->delete();
	}

	function findOneByUserId($userId) {
		return $this->findOneBy(array( self::COLUMN_SUBMITTER_ID  => $userId));
	}

}
?>