<?php

namespace App\Model;

use Nette\Database\Table\Selection;


class ConferenceHasAcmCategory extends Base {

    /** @var string */
	protected $tableName = 'conference_has_acm_category';

    /**
     * @param int $conferenceId
     * @return \Nette\Database\Table\Selection
     */
	public function findAllByConferenceId(int $conferenceId): Selection
    {
		return $this->findAllBy(array('conference_id' => $conferenceId));
	}

    /**
     * @param int $conference_id
     * @param array $category_ids
     */
	public function setAssociatedAcmCategories(int $conference_id, array $category_ids): void
    {
        if (!count($category_ids)) {
            return;
        }
        $this->fetchAll()->where('conference_id = ?', $conference_id)->delete();
        $arr = [];

		foreach ($category_ids as $category_id) {
			$arr[] = [
				'conference_id'		=>	$conference_id,
				'acm_category_id'	=>	$category_id
			];
		}

		$this->insertMulti($arr);
	}
}
