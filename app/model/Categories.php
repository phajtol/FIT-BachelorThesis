<?php

namespace App\Model;

class Categories extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'categories';


    public function getCategoriesTreeIds($id) {

        $treeIds = array();

        $category = $this->database->table('categories')->get($id);
        array_push($treeIds, array('id' => $category['id'], 'name' => $category['name']));

        $result = $this->database->table('categories')->where('categories_id = ?', $id);

        if (count($result) > 0) {
            foreach ($result as $row) {
                array_push($treeIds, array('id' => $row['id'], 'name' => $row['name']));
                $this->getChildrenIds($row['id'], 1, $treeIds);
            }
        }

        return $treeIds;
    }

    public function getChildrenIds($parentId, $level = 1, &$treeIds) {
        $result = $this->database->table('categories')->where('categories_id = ?', $parentId)->order('name ASC');
        if (count($result) > 0) {
            foreach ($result as $row) {
                array_push($treeIds, array('id' => $row['id'], 'name' => $row['name']));
                $this->getChildrenIds($row['id'], $level + 1, $treeIds);
            }
        }
    }

    public function deleteCategoryTreeBranch($id) {
        $result = $this->database->table('categories')->get($id);
        $this->deleteChildren($id);
        $this->deleteCategoriesHasPublication($id);
        if ($result) {
            $result->delete();
        }
    }

    public function deleteChildren($parentId, $level = 1) {
        $result = $this->database->table('categories')->where('categories_id = ?', $parentId);
        if (count($result) > 0) {
            foreach ($result as $row) {
                $this->deleteChildren($row['id'], $level + 1);
                $this->deleteCategoriesHasPublication($row['id']);
                $row->delete();
            }
        }
    }

    public function deleteCategoriesHasPublication($id) {
        $related = $this->database->table('categories_has_publication')->where(array("categories_id" => $id));
        foreach ($related as $rel) {
            $rel->delete();
        }
    }

    public function getParentCategories() {
        return $this->database->table('categories')->where('categories_id IS NULL OR categories_id = ?', 0)->order('name ASC')->fetchPairs('id', 'name');
    }

    public function findOneByName($name){
        return $this->findOneBy(array('name' => $name));
    }

}
