<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 29.3.2015
 * Time: 19:39
 */

namespace App\Model;


class AcmCategory extends BaseCategory {

	protected $tableName = 'acm_category';

	/**
	 * @return string table name
	 */
	protected function getTableName() {
		return "acm_category";
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
			'conference_has_acm_category'	=>	'acm_category_id'
		);
	}

}