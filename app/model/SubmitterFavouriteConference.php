<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;


class SubmitterFavouriteConference extends Base {

    /** @var string  */
	protected $tableName = 'submitter_favourite_conference';

    /**
     * @param int $id
     * @return Selection
     */
	public function getAllByUserId(int $id): Selection
    {
		return $this->findAllBy(array('submitter_id' => $id));
	}

    /**
     * @param int $conferenceId
     * @param int $userId
     * @return \Nette\Database\Table\ActiveRow
     */
	public function associateFavouriteConference(int $conferenceId, int $userId): ActiveRow
    {
		return $this->createOrUpdate(array(
			'submitter_id'  => $userId,
			'conference_id' =>  $conferenceId
		));
	}

    /**
     * @param int $conferenceId
     * @param int $userId
     * @return int - affected rows in db
     */
	public function detachFavouriteConference(int $conferenceId, int $userId): int {
		return $this->findOneBy([
			'submitter_id'  => $userId,
			'conference_id' =>  $conferenceId
		])->delete();
	}

    /**
     * @param int $userId
     * @param int $conferenceId
     * @return bool
     */
	public function isHisFavourite(int $userId, int $conferenceId): bool
    {
		return $this->findOneBy(array(
			'submitter_id'  => $userId,
			'conference_id' =>  $conferenceId
		)) ? true : false;
	}

    /**
     * @param int $userId
     * @return array
     */
	public function getUserFavouriteConferencesIds(int $userId): array
    {
		$res = $this->findAllBy(array('submitter_id' => $userId));
		$ids = array();

		foreach($res as $rec){
			$ids[] = $rec->conference_id;
		}

		return $ids;
	}
}
