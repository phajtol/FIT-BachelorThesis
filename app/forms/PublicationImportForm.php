<?php

use Nette\Application\UI,
    Nette\ComponentModel\IContainer,
    Nette\Diagnostics\Debugger;

class PublicationImportForm extends UI\Form {

    public function __construct(IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);
        $this->addTextArea('definition', 'Definition', 25, 25)->addRule($this::MAX_LENGTH, 'Definition is way too long', 20000)->setRequired('Definition is required.')->addRule(PublicationFormRules::BIBTEX_VALIDATE_STRUCTURE, "Structure is wrong.", $parent);
        $this->addRadioList('type', 'Type', array('bibtex' => 'Bibtex', 'endnote' => 'EndNote', 'refworks' => 'RefWorks'))->setDefaultValue('bibtex')->setRequired('Type is required.');
        $this->addSubmit('send', 'Import');
    }

}
