<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 31.3.2015
 * Time: 16:14
 */

namespace App\Model;


class ConferenceHasAcmCategory extends Base {

	protected $tableName = 'conference_has_acm_category';

	public function findAllByConferenceId($conferenceId) {
		return $this->findAllBy(array('conference_id' => $conferenceId));
	}

	public function setAssociatedAcmCategories($conference_id, $category_ids) {
		$this->fetchAll()->where('conference_id = ?', $conference_id)->delete();

		if(!count($category_ids)) return;

		$arr = [];

		foreach($category_ids as $category_id){
			$arr[] = array(
				'conference_id'		=>	$conference_id,
				'acm_category_id'	=>	$category_id
			);
		}

		$this->insertMulti($arr);
	}

}