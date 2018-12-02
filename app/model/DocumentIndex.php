<?php

namespace App\Model;


use Nette\Database\Table\ActiveRow;

class DocumentIndex extends Base {

    /** @var string */
	protected $tableName = 'document_index';

    /**
     * @param string $name
     * @return \Nette\Database\Table\ActiveRow|FALSE
     */
	public function findOneByName(string $name)
    {
		return $this->findOneBy(array('name'  =>  $name));
	}

    /**
     * @param int $id
     */
	public function deleteWithAssociatedRecords(int $id): void
    {
		$this->database->table('conference_year_is_indexed')->where(array('document_index_id' => $id))->delete();
		$this->delete($id);
	}

}