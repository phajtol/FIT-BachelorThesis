<?php

use Nette\Application\UI;
use Nette\ComponentModel\IContainer;

class PublicationShowAllSearchForm extends UI\Form {

    /**
     * PublicationShowAllSearchForm constructor.
     * @param IContainer|NULL $parent
     * @param string|NULL $name
     */
    public function __construct(IContainer $parent = NULL, string $name = NULL) {
        parent::__construct($parent, $name);

        $this->addText('search', 'Search')
            ->addRule($this::MAX_LENGTH, 'Search is way too long', 100);

        $this->addSubmit('send', 'Send');
    }

}
