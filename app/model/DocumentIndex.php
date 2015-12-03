<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 15.4.2015
 * Time: 16:40
 */

namespace App\Model;


class DocumentIndex extends Base {

	protected $tableName = 'document_index';

	public function findOneByName($name) {
		return $this->findOneBy(array('name'  =>  $name));
	}

	public function deleteWithAssociatedRecords($id) {
		$this->database->table('conference_year_is_indexed')->where(array('document_index_id' => $id))->delete();
		$this->delete($id);
	}

}