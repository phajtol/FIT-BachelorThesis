<?php

namespace App\Presenters;

use Nette,
    App\Model;

class PublicationCategoryPresenter extends SecuredPresenter {

  /** @var Model\Categories @inject */
  public $categoriesModel;

  /** @var Model\CategoriesHasPublication @inject */
  public $categoriesHasPublicationModel;

    public function createComponentPublicationCategoryList($name){
        $c = new \App\Components\PublicationCategoryList\PublicationCategoryListComponent(
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
