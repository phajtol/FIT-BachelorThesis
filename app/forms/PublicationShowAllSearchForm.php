<?php

use Nette\Application\UI,
    Nette\ComponentModel\IContainer;

class PublicationShowAllSearchForm extends UI\Form {

    public function __construct(IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $this->addText('search', 'Search')->addRule($this::MAX_LENGTH, 'Search is way too long', 100);
        $this->addSubmit('send', 'Send');
    }

}
