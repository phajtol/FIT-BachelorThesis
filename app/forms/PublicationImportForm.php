<?php

use Nette\Application\UI;
use Nette\ComponentModel\IContainer;

class PublicationImportForm extends UI\Form {

    /**
     * PublicationImportForm constructor.
     * @param IContainer|NULL $parent
     * @param string|NULL $name
     */
    public function __construct(IContainer $parent = NULL, string $name = NULL)
    {
        parent::__construct($parent, $name);
        $this->addTextArea('definition', 'Definition', 25, 25)
            ->addRule($this::MAX_LENGTH, 'Definition is way too long', 20000)
            ->setRequired('Definition is required.')
            ->addRule(PublicationFormRules::BIBTEX_VALIDATE_STRUCTURE, "Structure is wrong.", $parent);

        $this->addRadioList('type', 'Type', ['bibtex' => 'Bibtex', 'endnote' => 'EndNote', 'refworks' => 'RefWorks'])
            ->setDefaultValue('bibtex')
            ->setRequired('Type is required.');

        $this->addSubmit('send', 'Import');
    }

}
