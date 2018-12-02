<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 29.3.2015
 * Time: 19:38
 */

namespace App\Components\AcmCategoryList;


use App\CrudComponents\AcmCategory\AcmCategoryCrud;

class AcmCategoryListComponent extends \App\Components\CategoryList\CategoryListComponent {

	/**
	 * @var \App\Model\AcmCategory
	 */
	protected $acmCategoryModel;

	/**
	 * @var \App\Model\ConferenceHasAcmCategory
	 */
	protected $conferenceHasAcmCategoryModel;

	/**
	 * @var \App\Factories\IConferenceCrudFactory
	 */
	//protected $conferenceCrudFactory;

	/**
	 * @var \Nette\Security\User
	 */
	protected $loggedUser;



	public function __construct(
	    \Nette\Security\User $loggedUser,
		\App\Model\AcmCategory $acmCategoryModel,
		\App\Model\ConferenceHasAcmCategory $conferenceHasAcmCategoryModel,
		//\App\Factories\IConferenceCrudFactory $conferenceCrudFactory,
		\Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->loggedUser = $loggedUser;
		$this->acmCategoryModel = $acmCategoryModel;
		$this->conferenceHasAcmCategoryModel = $conferenceHasAcmCategoryModel;
		//$this->conferenceCrudFactory = $conferenceCrudFactory;
	}

	protected function getRecords(): array
    {
		$records = array();
		$categories = $this->acmCategoryModel->fetchAll()->order('name ASC');

		foreach ($categories as $cat) {
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
	protected function normalizeRecord($cat): array
    {
		return array(
			'id'		=>	$cat->id,
			'name'		=>	$cat->name,
			'parent_id'	=>	$cat->parent_id
		);
	}


	protected function createCrudComponent($name): AcmCategoryCrud
    {
		return new AcmCategoryCrud(
		    $this->loggedUser,
            $this->acmCategoryModel,
            $this->conferenceHasAcmCategoryModel,
            null,
            $name
		);
	}

    /**
     * This function implements moving the category in the tree (change parent)
     * @param $categoryId int Id of the category to be moved
     * @param $newParentCategoryId int Id of the new parent category
     * @return int - affected rows in database
     */
	protected function moveCategory(int $categoryId, int $newParentCategoryId): int
    {
		return $this->acmCategoryModel->moveCategory($categoryId, $newParentCategoryId);
	}


}