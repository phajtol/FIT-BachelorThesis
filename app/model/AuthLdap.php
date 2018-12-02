<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;


class AuthLdap Extends Base {

	/**
	 * Name of the database table
	 * @var string
	 */
	protected $tableName = 'auth_ldap';

	const COLUMN_LOGIN = 'login';
	const COLUMN_SUBMITTER_ID = 'submitter_id';

	/**
	 * @param string $login
	 * @return FALSE|\Nette\Database\Table\ActiveRow
	 */
	function findByLogin(string $login): ActiveRow
    {
		return $this->findOneBy(array(
			self::COLUMN_LOGIN	=>	$login
		));
	}

    /**
     * @param int $submitter_id
     * @param string $login
     * @return ActiveRow
     */
	function associateToSubmitter(int $submitter_id, string $login): ActiveRow
    {
		return $this->insert(array(
			self::COLUMN_SUBMITTER_ID 	=>	$submitter_id,
			self::COLUMN_LOGIN 			=>	$login
		));
	}

    /**
     * @param int $userId
     * @return int
     */
	function deleteFromSubmitter(int $userId): int
    {
		return $this->findAllBy(array( self::COLUMN_SUBMITTER_ID  => $userId))->delete();
	}

    /**
     * @param int $userId
     * @return FALSE|ActiveRow
     */
	function findOneByUserId(int $userId)
    {
		return $this->findOneBy(array( self::COLUMN_SUBMITTER_ID  => $userId));
	}
}
