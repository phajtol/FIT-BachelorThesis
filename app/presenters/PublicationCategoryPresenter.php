<?php

namespace App\Presenters;

use Nette,
    App\Model;

class PublicationCategoryPresenter extends SecuredPresenter {

    public function createComponentPublicationCategoryList($name){
        $c = new \App\Components\PublicationCategoryList\PublicationCategoryListComponent(
            $this->user,
            $this->context->Categories,
            $this->context->CategoriesHasPublication,
            $this, $name
        );

        $c->setHasControls(true);
        $c->setHasDnD(true);

        return $c;
    }

}
