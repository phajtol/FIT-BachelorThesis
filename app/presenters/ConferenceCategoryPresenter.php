<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 2.4.2015
 * Time: 22:22
 */

namespace App\Presenters;


class ConferenceCategoryPresenter extends SecuredPresenter {

	/**
	 * @var \App\Factories\IConferenceCategoryListFactory
	 */
	protected $conferenceCategoryListFactory;

	public function createComponentConferenceCategoryList($name){
		$c = $this->conferenceCategoryListFactory->create();

		$c->setHasControls(true);
		$c->setHasDnD(true);
		$c->setIsSelectable(false);

		return $c;
	}

	/**
	 * @param \App\Factories\IConferenceCategoryListFactory $conferenceCategoryListFactory
	 */
	public function injectConferenceCategoryListFactory(\App\Factories\IConferenceCategoryListFactory $conferenceCategoryListFactory) {
		$this->conferenceCategoryListFactory = $conferenceCategoryListFactory;
	}

}