<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 2.4.2015
 * Time: 22:06
 */

namespace App\Model;


class ConferenceCategory extends BaseCategory {
	/**
	 * @return string table name
	 */
	protected function getTableName() {
		return "conference_category";
	}

	/**
	 * @return string ID column name
	 */
	protected function getIdColumnName() {
		return "id";
	}

	/**
	 * @return string parent record ID column name
	 */
	protected function getParentIdColumnName() {
		return "parent_id";
	}

	/**
	 * @return string category name column name
	 */
	protected function getNameColumnName() {
		return "name";
	}

	/**
	 * @return array associated tables from which records will be deleted when category is deleted - array('assoc_table' => 'relation_column_name')
	 */
	protected function getRelatedTables() {
		return array(
			'conference_has_category'	=>	'conference_category_id',
			'cu_group_has_conference_category'	=>	'conference_category_id'
		);
	}

	public function insert($data) {


		return parent::insert($data);
	}


}