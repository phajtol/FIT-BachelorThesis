<?php

use Nette\Application\UI;
use Nette\ComponentModel\IContainer;

class SearchForm extends UI\Form {

    /**
     * SearchForm constructor.
     * @param IContainer|NULL $parent
     * @param string|NULL $name
     */
    public function __construct(IContainer $parent = NULL, string $name = NULL) {
        parent::__construct($parent, $name);

        $this->addText('keywords', 'Keywords');

        $this->addSubmit('send', 'Search');

        $this->setDefaults($parent->data);
    }

}
