<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;


class ConferenceCategory extends BaseCategory {
	/**
	 * @return string - table name
	 */
	protected function getTableName(): string
    {
		return "conference_category";
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
		return [
			'conference_has_category'	=>	'conference_category_id',
			'cu_group_has_conference_category'	=>	'conference_category_id'
		];
	}

    /**
     * @param array $data
     * @return ActiveRow
     */
	public function insert($data): ActiveRow
    {
		return parent::insert($data);
	}

}
