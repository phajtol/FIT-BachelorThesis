<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 15.4.2015
 * Time: 16:41
 */

namespace App\Model;


class ConferenceYearIsIndexed extends Base {

	protected $tableName = 'conference_year_is_indexed';


	public function findAllByConferenceYearId($conferenceYearId) {
		return $this->findAllBy(array('conference_year_id' => $conferenceYearId));
	}

	public function setAssociatedDocumentIndexes($conferenceYearId, $indexesIds) {
		$this->fetchAll()->where('conference_year_id = ?', $conferenceYearId)->delete();

		if(!count($indexesIds)) return;

		$arr = [];

		foreach($indexesIds as $indexId){
			$arr[] = array(
				'conference_year_id'	=>	$conferenceYearId,
				'document_index_id' 	=>	$indexId
			);
		}

		$this->insertMulti($arr);
	}

	public function getAssociatedDocumentIndexes($conferenceYearId) {
		$documentIndexes = [];
		$resIndexed = $this->findAllByConferenceYearId($conferenceYearId)
			->order('document_index.name ASC');
		foreach($resIndexed as $recIndexed) {
			$documentIndexes[] = $recIndexed->ref('document_index');
		}
		return $documentIndexes;
	}

}
?>