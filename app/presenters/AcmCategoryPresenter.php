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
	 * @var \App\Factories\IAcmCategoryListFactory @inject
	 */
	public $acmCategoryListFactory;

	public function createComponentAcmCategoryList($name){
		$c = $this->acmCategoryListFactory->create();
		$c->setHasControls(true);
		$c->setHasDnD(true);

		return $c;
	}

}
