<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 31.3.2015
 * Time: 15:52
 */

namespace App\Model;


abstract class BaseCategory extends Base {

	protected $idColumnName;
	protected $nameColumnName;
	protected $parentIdColumnName;
	protected $relatedTables;

	/**
	 * @return string table name
	 */
	protected abstract function getTableName();

	/**
	 * @return string ID column name
	 */
	protected abstract function getIdColumnName();

	/**
	 * @return string parent record ID column name
	 */
	protected abstract function getParentIdColumnName();

	/**
	 * @return string category name column name
	 */
	protected abstract function getNameColumnName();

	/**
	 * @return array associated tables from which records will be deleted when category is deleted - array('assoc_table' => 'relation_column_name')
	 */
	protected abstract function getRelatedTables();

	/**
	 * @param \Nette\Database\Context $db
	 */

	public function __construct(\Nette\Database\Context $db) {
		$this->tableName = $this->getTableName();

		$this->idColumnName = $this->getIdColumnName();
		$this->nameColumnName = $this->getNameColumnName();
		$this->parentIdColumnName = $this->getParentIdColumnName();
		$this->relatedTables = $this->getRelatedTables();

		parent::__construct($db);
	}

	/**
	 * @param $id int id of the parent category
	 * @return array array(array(CAT_ID, CAT_NAME), ..)
	 */
	public function getCategoriesTreeIds($id) {
		$treeIds = array();

		$category = $this->fetchAll()->get($id);
		array_push($treeIds, array('id' => $category[$this->idColumnName], 'name' => $category[$this->nameColumnName]));

		$result = $this->fetchAll()->where($this->parentIdColumnName . ' = ?', $id);

		if (count($result) > 0) {
			foreach ($result as $row) {
				array_push($treeIds, array('id' => $row[$this->idColumnName], 'name' => $row[$this->nameColumnName]));
				$this->getChildrenIds($row[$this->idColumnName], 1, $treeIds);
			}
		}

		return $treeIds;
	}

	public function getCategoriesTreeIdsOnly($id) {
		$fullInfo = $this->getCategoriesTreeIds($id);

		$ids = array();
		foreach($fullInfo as $fi) array_push($ids, $fi['id']);

		return $ids;
	}

	public function getChildrenIds($parentId, $level = 1, &$treeIds) {
		$result = $this->fetchAll()->where($this->parentIdColumnName . ' = ?', $parentId)->order($this->nameColumnName . ' ASC');
		if (count($result) > 0) {
			foreach ($result as $row) {
				array_push($treeIds, array('id' => $row[$this->idColumnName], 'name' => $row[$this->nameColumnName]));
				$this->getChildrenIds($row[$this->idColumnName], $level + 1, $treeIds);
			}
		}
	}

	public function deleteCategoryTreeBranch($id) {
		$result = $this->find($id);
		$this->deleteChildren($id);
		$this->deleteRelatedEntities($id);
		if ($result) {
			$result->delete();
		}
	}

	public function deleteChildren($parentId, $level = 1) {
		$result = $this->fetchAll()->where($this->parentIdColumnName . ' = ?', $parentId);
		if (count($result) > 0) {
			foreach ($result as $row) {
				$this->deleteChildren($row[$this->idColumnName], $level + 1);
				$this->deleteRelatedEntities($row[$this->idColumnName]);
				$row->delete();
			}
		}
	}

	/**
	 * This method effectively fetches ids of the subtree elements. The hierarchy is flatten.
	 * @param $cat_ids array array of category ids, the whole subtree will be returned
	 * @return array unsorted array of ids of subtree elements
	 */
	public function getAllSubtreeIds($cat_ids) {
		$children = $this->fetchAll()->where($this->parentIdColumnName . ' IN (?)', $cat_ids);
		$children_ids = [];

		$idColName = $this->idColumnName;
		foreach($children as $child) $children_ids[] = $child->$idColName;

		if(!count($children_ids)) return $cat_ids; // no children in given level found, nothing more to explore

		return array_merge($cat_ids, $this->getAllSubtreeIds($children_ids));	// more to explore, do recursion on the next level
	}

	public function deleteRelatedEntities($id) {
		foreach($this->relatedTables as $tableName => $relationColumnName) {
			$this->database->table($tableName)->where(array($relationColumnName => $id))->delete();
		}
	}

	public function findOneByName($name){
		return $this->findOneBy(array($this->nameColumnName => $name));
	}


	public function moveCategory($id, $parent_id) {
		return $this->find($id)->update(array(
			$this->getParentIdColumnName()  =>  $parent_id
		));
	}

}