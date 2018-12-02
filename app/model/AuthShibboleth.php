<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;


class AuthShibboleth extends Base {

    /** @var string  */
	protected $tableName = 'auth_shibboleth';

	const COLUMN_USERNAME = 'username';
	const COLUMN_EMAIL = 'email';
	const COLUMN_SUBMITTER_ID = 'submitter_id';

    /**
     * @param $userName
     * @param $email
     * @return FALSE|\Nette\Database\Table\ActiveRow
     */
	function findByUsernameAndEmail(string $userName, string $email)
    {
		return $this->findOneBy(array(
			self::COLUMN_USERNAME   =>	$userName,
			self::COLUMN_EMAIL      =>  $email
		));
	}

    /**
     * @param int $submitterId
     * @param string $userName
     * @param string $email
     * @return ActiveRow
     */
	function associateToSubmitter(int $submitterId, string $userName, string $email): ActiveRow
    {
		return $this->createOrUpdate(array(
			self::COLUMN_SUBMITTER_ID 	=>	$submitterId,
			self::COLUMN_USERNAME		=>	$userName,
			self::COLUMN_EMAIL          =>  $email
		));
	}

    /**
     * @param int $userId
     * @return int - affected rows
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
		return $this->findOneBy(array(self::COLUMN_SUBMITTER_ID  => $userId));
	}


}