<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 7.4.2015
 * Time: 19:46
 */

namespace App\Model;


class SubmitterFavouriteConference extends Base {

	protected $tableName = 'submitter_favourite_conference';


	public function getAllByUserId($id){
		return $this->findAllBy(array('submitter_id' => $id));
	}

	public function associateFavouriteConference($conferenceId, $userId) {
		return $this->createOrUpdate(array(
			'submitter_id'  => $userId,
			'conference_id' =>  $conferenceId
		));
	}

	public function detachFavouriteConference($conferenceId, $userId) {
		return $this->findOneBy(array(
			'submitter_id'  => $userId,
			'conference_id' =>  $conferenceId
		))->delete();
	}

	public function isHisFavourite($userId, $conferenceId) {
		return $this->findOneBy(array(
			'submitter_id'  => $userId,
			'conference_id' =>  $conferenceId
		)) ? true : false;
	}

	public function getUserFavouriteConferencesIds($userId) {
		$res = $this->findAllBy(array('submitter_id' => $userId));
		$ids = array();
		foreach($res as $rec){
			$ids[] = $rec->conference_id;
		}
		return $ids;
	}

}