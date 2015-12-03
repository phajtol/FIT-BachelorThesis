<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 2.4.2015
 * Time: 22:07
 */

namespace App\Model;


class ConferenceHasCategory extends Base {

	protected $tableName = 'conference_has_category';

	public function findAllByConferenceId($conferenceId) {
		return $this->findAllBy(array('conference_id' => $conferenceId));
	}

	public function setAssociatedConferenceCategories($conference_id, $category_ids) {
		$this->fetchAll()->where('conference_id = ?', $conference_id)->delete();

		if(!count($category_ids)) return;

		$arr = [];

		foreach($category_ids as $category_id){
			$arr[] = array(
				'conference_id'				=>	$conference_id,
				'conference_category_id'	=>	$category_id
			);
		}

		$this->insertMulti($arr);
	}

}