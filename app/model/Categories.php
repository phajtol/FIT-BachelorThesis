<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;

class Categories extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'categories';


    /**
     * @param int $id
     * @return array
     */
    public function getCategoriesTreeIds(int $id): array
    {
        $treeIds = [];
        $category = $this->database->table('categories')->get($id);

        array_push($treeIds, ['id' => $category['id'], 'name' => $category['name']]);

        $result = $this->database->table('categories')->where('categories_id = ?', $id);

        if (count($result) > 0) {
            foreach ($result as $row) {
                array_push($treeIds, ['id' => $row['id'], 'name' => $row['name']]);
                $this->getChildrenIds($row['id'], $treeIds, 1);
            }
        }

        return $treeIds;
    }

    /**
     * @param $parentId
     * @param int $level
     * @param $treeIds
     */
    public function getChildrenIds(int $parentId, array &$treeIds, int $level = 1): void
    {
        $result = $this->database->table('categories')->where('categories_id = ?', $parentId)->order('name ASC');

        if (count($result) > 0) {
            foreach ($result as $row) {
                array_push($treeIds, array('id' => $row['id'], 'name' => $row['name']));
                $this->getChildrenIds($row['id'], $treeIds, $level + 1);
            }
        }
    }

    /**
     * @param int $id
     */
    public function deleteCategoryTreeBranch(int $id): void
    {
        $result = $this->database->table('categories')->get($id);

        $this->deleteChildren($id);
        $this->deleteCategoriesHasPublication($id);

        if ($result) {
            $result->delete();
        }
    }

    /**
     * @param int $parentId
     * @param int $level
     */
    public function deleteChildren(int $parentId, int $level = 1): void
    {
        $result = $this->database->table('categories')->where('categories_id = ?', $parentId);

        if (count($result) > 0) {
            foreach ($result as $row) {
                $this->deleteChildren($row['id'], $level + 1);
                $this->deleteCategoriesHasPublication($row['id']);
                $row->delete();
            }
        }
    }

    /**
     * @param int $id
     */
    public function deleteCategoriesHasPublication(int $id): void {
        $related = $this->database->table('categories_has_publication')->where(["categories_id" => $id]);

        foreach ($related as $rel) {
            $rel->delete();
        }
    }

    /**
     * @return array
     */
    public function getParentCategories(): array
    {
        return $this->database->table('categories')
            ->where('categories_id IS NULL OR categories_id = ?', 0)
            ->order('name ASC')
            ->fetchPairs('id', 'name');
    }

    /**
     * @param string $name
     * @return FALSE|\Nette\Database\Table\ActiveRow
     */
    public function findOneByName(string $name)
    {
        return $this->findOneBy(['name' => $name]);
    }

}
