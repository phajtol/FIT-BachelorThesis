<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 31.3.2015
 * Time: 16:49
 */

namespace App\Presenters;


class AcmCategoryPresenter extends SecuredPresenter {

	/**
	 * @var \App\Factories\IAcmCategoryListFactory
	 */
	protected $acmCategoryListFactory;

	public function createComponentAcmCategoryList($name){
		$c = $this->acmCategoryListFactory->create();
		$c->setHasControls(true);
		$c->setHasDnD(true);

		return $c;
	}

	/**
	 * @param \App\Factories\IAcmCategoryListFactory $acmCategoryListFactory
	 */
	public function injectAcmCategoryListFactory(\App\Factories\IAcmCategoryListFactory $acmCategoryListFactory) {
		$this->acmCategoryListFactory = $acmCategoryListFactory;
	}


}