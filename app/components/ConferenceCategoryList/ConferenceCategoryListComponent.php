<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 2.4.2015
 * Time: 22:19
 */

namespace App\Components\ConferenceCategoryList;


use App\Components\CategoryList\CategoryListComponent;

class ConferenceCategoryListComponent extends CategoryListComponent {


	/**
	 * @var \App\Model\ConferenceCategory
	 */
	protected $conferenceCategoryModel;

	/**
	 * @var \App\Model\ConferenceHasCategory
	 */
	protected $conferenceHasCategoryModel;

	/**
	 * @var \Nette\Security\User
	 */
	protected $loggedUser;



	public function __construct(
		\Nette\Security\User $loggedUser,
		\App\Model\ConferenceCategory $conferenceCategoryModel,
		\App\Model\ConferenceHasCategory $conferenceHasCategoryModel,

		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {

		parent::__construct($parent, $name);

		$this->loggedUser = $loggedUser;
		$this->conferenceCategoryModel = $conferenceCategoryModel;
		$this->conferenceHasCategoryModel = $conferenceHasCategoryModel;
	}

	protected function getRecords() {
		$records = array();

		$categories = $this->conferenceCategoryModel->fetchAll()->order('name ASC');

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
	protected function normalizeRecord($cat): array {
		return array(
			'id'		=>	$cat->id,
			'name'		=>	$cat->name,
			'parent_id'	=>	$cat->parent_id
		);
	}


	protected function createCrudComponent($name) {
		return new \App\CrudComponents\ConferenceCategory\ConferenceCategoryCrud(
			$this->loggedUser,
			$this->conferenceCategoryModel,
			$this->conferenceHasCategoryModel,
			$this, $name
		);
	}

    /**
     * This function implements moving the category in the tree (change parent)
     * @param $categoryId int Id of the category to be moved
     * @param $newParentCategoryId int Id of the new parent category
     * @return int
     */
	protected function moveCategory(int $categoryId, int $newParentCategoryId): int {
		return $this->conferenceCategoryModel->moveCategory($categoryId, $newParentCategoryId);
	}


}