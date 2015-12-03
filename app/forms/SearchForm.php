<?php

use Nette\Application\UI,
    Nette\ComponentModel\IContainer;

class SearchForm extends UI\Form {

    public function __construct(IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $this->addText('keywords', 'Keywords');
        $this->addSubmit('send', 'Search');
        $this->setDefaults($parent->data);
    }

}
