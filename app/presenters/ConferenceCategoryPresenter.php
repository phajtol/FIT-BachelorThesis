<?php

namespace App\Presenters;

use App\Components\ConferenceCategoryList\ConferenceCategoryListComponent;


class ConferenceCategoryPresenter extends SecuredPresenter {

	/** @var \App\Factories\IConferenceCategoryListFactory @inject */
	public $conferenceCategoryListFactory;


    /**
     * @param string $name
     * @return \App\Components\ConferenceCategoryList\ConferenceCategoryListComponent
     */
	public function createComponentConferenceCategoryList(string $name): ConferenceCategoryListComponent
    {
        $c = $this->conferenceCategoryListFactory->create();

        $c->setHasControls(true);
        $c->setHasDnD(true);
        $c->setIsSelectable(false);

        return $c;
    }

}
