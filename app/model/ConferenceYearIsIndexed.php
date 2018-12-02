<?php

namespace App\Model;

use Nette\Database\Table\Selection;


class ConferenceYearIsIndexed extends Base {

    /** @var string */
	protected $tableName = 'conference_year_is_indexed';


    /**
     * @param int $conferenceYearId
     * @return \Nette\Database\Table\Selection
     */
	public function findAllByConferenceYearId(int $conferenceYearId): Selection
    {
		return $this->findAllBy(['conference_year_id' => $conferenceYearId]);
	}

    /**
     * @param int $conferenceYearId
     * @param array $indexesIds
     */
	public function setAssociatedDocumentIndexes(int $conferenceYearId, array $indexesIds): void
    {
        if (!count($indexesIds)) {
            return;
        }
        $this->fetchAll()->where('conference_year_id = ?', $conferenceYearId)->delete();
        $arr = [];

		foreach ($indexesIds as $indexId) {
			$arr[] = [
				'conference_year_id'	=>	$conferenceYearId,
				'document_index_id' 	=>	$indexId
			];
		}

		$this->insertMulti($arr);
	}

    /**
     * @param int $conferenceYearId
     * @return array
     */
	public function getAssociatedDocumentIndexes(int $conferenceYearId): array
    {
		$documentIndexes = [];
		$resIndexed = $this->findAllByConferenceYearId($conferenceYearId)->order('document_index.name ASC');

		foreach ($resIndexed as $recIndexed) {
			$documentIndexes[] = $recIndexed->ref('document_index');
		}

		return $documentIndexes;
	}
}
