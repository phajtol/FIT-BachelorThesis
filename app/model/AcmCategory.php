<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 29.3.2015
 * Time: 19:39
 */

namespace App\Model;


class AcmCategory extends BaseCategory {

    /** @var string */
	protected $tableName = 'acm_category';

	/**
	 * @return string - table name
	 */
	protected function getTableName(): string
    {
		return "acm_category";
	}

	/**
	 * @return string - ID column name
	 */
	protected function getIdColumnName(): string
    {
		return "id";
	}

	/**
	 * @return string - parent record ID column name
	 */
	protected function getParentIdColumnName(): string
    {
		return "parent_id";
	}

	/**
	 * @return string - category name column name
	 */
	protected function getNameColumnName(): string
    {
		return "name";
	}

	/**
	 * @return array - associated tables from which records will be deleted when category is deleted - array('assoc_table' => 'relation_column_name')
	 */
	protected function getRelatedTables(): array
    {
		return array(
			'conference_has_acm_category'	=>	'acm_category_id'
		);
	}

}