<?php

namespace App\Presenters;

use App\Components\PublicationCategoryList\PublicationCategoryListComponent;
use App\Model;


class PublicationCategoryPresenter extends SecuredPresenter {

    /** @var Model\Categories @inject */
    public $categoriesModel;

    /** @var Model\CategoriesHasPublication @inject */
    public $categoriesHasPublicationModel;


    /**
     * @param $name
     * @return \App\Components\PublicationCategoryList\PublicationCategoryListComponent
     */
    public function createComponentPublicationCategoryList($name): PublicationCategoryListComponent{
        $c = new PublicationCategoryListComponent(
            $this->user,
            $this->categoriesModel,
            $this->categoriesHasPublicationModel,
            $this, $name
        );

        $c->setHasControls(true);
        $c->setHasDnD(true);

        return $c;
    }

}
