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
	 * @var \App\Factories\IConferenceCategoryListFactory @inject
	 */
	public $conferenceCategoryListFactory;

	public function createComponentConferenceCategoryList($name){
		$c = $this->conferenceCategoryListFactory->create();

		$c->setHasControls(true);
		$c->setHasDnD(true);
		$c->setIsSelectable(false);

		return $c;
	}


}
