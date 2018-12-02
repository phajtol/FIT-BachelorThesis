<?php

namespace App\Presenters;

use App\Components\AcmCategoryList\AcmCategoryListComponent;


class AcmCategoryPresenter extends SecuredPresenter {

	/** @var \App\Factories\IAcmCategoryListFactory @inject */
	public $acmCategoryListFactory;

    /**
     * @return \App\Components\AcmCategoryList\AcmCategoryListComponent
     */
	public function createComponentAcmCategoryList(): AcmCategoryListComponent
    {
		$c = $this->acmCategoryListFactory->create();
		$c->setHasControls(true);
		$c->setHasDnD(true);

		return $c;
	}

}
