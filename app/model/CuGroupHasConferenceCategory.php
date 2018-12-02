<?php

/**
 * warning! the realtions will be incomplete. when fetching related conferences, all the category_ids must be fetched
 * through ConferenceCategory->getAllSubtreeIds
 */

namespace App\Model;


class CuGroupHasConferenceCategory extends Base {

    /** @var string */
	protected $tableName = 'cu_group_has_conference_category';

	public function setAssociatedConferenceCategories($cu_group_id, $category_ids): void
    {
		$this->fetchAll()->where('cu_group_id = ?', $cu_group_id)->delete();

		if (!count($category_ids)) {
		    return;
        }

		$arr = [];

		foreach ($category_ids as $category_id) {
			$arr[] = [
				'cu_group_id'				=>	$cu_group_id,
				'conference_category_id'	=>	$category_id
			];
		}

		$this->insertMulti($arr);
	}

}