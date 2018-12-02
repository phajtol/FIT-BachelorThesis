<?php

namespace App\Components\PublicationCategoryList;


use App\CrudComponents\PublicationCategory\PublicationCategoryCrud;

class PublicationCategoryListComponent extends \App\Components\CategoryList\CategoryListComponent {

	/** @var \App\Model\Categories */
	protected $publicationCategoryModel;

	/** @var \App\Model\CategoriesHasPublication */
	protected $categoriesHasPublicationModel;

	/** @var \Nette\Security\User */
	protected $loggedUser;


    /**
     * PublicationCategoryListComponent constructor.
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\Categories $publicationCategoryModel
     * @param \App\Model\CategoriesHasPublication $categoriesHasPublicationModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\Nette\Security\User $loggedUser,
		                        \App\Model\Categories $publicationCategoryModel,
		                        \App\Model\CategoriesHasPublication $categoriesHasPublicationModel,
		                        \Nette\ComponentModel\IContainer $parent = NULL,
                                string $name = NULL)
    {
        parent::__construct();

        if ($parent) {
            $parent->addComponent($this, $name);
        }

		$this->loggedUser = $loggedUser;
		$this->publicationCategoryModel = $publicationCategoryModel;
		$this->categoriesHasPublicationModel = $categoriesHasPublicationModel;
	}

    /**
     * @return array
     */
	protected function getRecords(): array
    {
		$records = [];
		$categories = $this->publicationCategoryModel->fetchAll()->order('name ASC');

		foreach($categories as $cat) {
			$records[] = $this->normalizeRecord($cat);
		}

		return $records;
	}

    /**
     * This function has to return the normalized record array. It must contain following:
     *    - id - id of the record
     *  - parent_id - id of the parent record
     *  - name - name of the record
     * @param $cat
     * @return array array('id' => .., 'parent_id' => .., 'name' => ..)
     */
	protected function normalizeRecord($cat): array {
		return [
			'id'		=>	$cat->id,
			'name'		=>	$cat->name,
			'parent_id'	=>	$cat->categories_id
		];
	}

    /**
     * @param string $name
     * @return PublicationCategoryCrud
     */
	protected function createCrudComponent(string $name): PublicationCategoryCrud
    {
		return new PublicationCategoryCrud(
		    $this->loggedUser,
            $this->publicationCategoryModel,
            $this->categoriesHasPublicationModel,
			$this,
            $name
		);
	}

    /**
     * This function implements moving the category in the tree (change parent)
     * @param int $categoryId - Id of the category to be moved
     * @param int $newParentCategoryId - Id of the new parent category
     * @return int
     */
	protected function moveCategory(int $categoryId, int $newParentCategoryId): int
    {
		return $this->publicationCategoryModel->find($categoryId)->update([
			'categories_id'     =>  $newParentCategoryId
		]);
	}


}