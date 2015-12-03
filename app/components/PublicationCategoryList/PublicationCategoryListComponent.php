<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 28.3.2015
 * Time: 20:23
 */

namespace App\Components\PublicationCategoryList;


class PublicationCategoryListComponent extends \App\Components\CategoryList\CategoryListComponent {

	/**
	 * @var \App\Model\Categories
	 */
	protected $publicationCategoryModel;

	/**
	 * @var \App\Model\CategoriesHasPublication
	 */
	protected $categoriesHasPublicationModel;

	/**
	 * @var \Nette\Security\User
	 */
	protected $loggedUser;


	public function __construct(
		\Nette\Security\User $loggedUser,
		\App\Model\Categories $publicationCategoryModel,
		\App\Model\CategoriesHasPublication $categoriesHasPublicationModel,

		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {

		parent::__construct($parent, $name);

		$this->loggedUser = $loggedUser;
		$this->publicationCategoryModel = $publicationCategoryModel;
		$this->categoriesHasPublicationModel = $categoriesHasPublicationModel;
	}

	protected function getRecords() {
		$records = array();

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
	 * @return array array('id' => .., 'parent_id' => .., 'name' => ..)
	 */
	protected function normalizeRecord($cat) {
		return array(
			'id'		=>	$cat->id,
			'name'		=>	$cat->name,
			'parent_id'	=>	$cat->categories_id
		);
	}


	protected function createCrudComponent($name) {
		return new \App\CrudComponents\PublicationCategory\PublicationCategoryCrud(
			$this->loggedUser,
			$this->publicationCategoryModel,
			$this->categoriesHasPublicationModel,
			$this, $name
		);
	}

	/**
	 * This function implements moving the category in the tree (change parent)
	 * @param $categoryId int Id of the category to be moved
	 * @param $newParentCategoryId int Id of the new parent category
	 */
	protected function moveCategory($categoryId, $newParentCategoryId) {
		return $this->publicationCategoryModel->find($categoryId)->update(array(
			'categories_id'     =>  $newParentCategoryId
		));
	}


}