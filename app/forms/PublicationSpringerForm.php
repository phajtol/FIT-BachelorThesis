<?php

use Nette\Application\UI;
use Nette\ComponentModel\IContainer;

class PublicationSpringerForm extends UI\Form {

    /**
     * PublicationSpringerForm constructor.
     * @param IContainer|NULL $parent
     * @param string $name
     */
    public function __construct(IContainer $parent = NULL, string $name = NULL)
    {
        parent::__construct($parent, $name);

        $this->addText('id_springer', 'Identifier', 25, 25)
            ->addRule($this::MAX_LENGTH, 'Identifier is way too long', 100)
            ->setRequired('Identifier is required.');

        $this->addRadioList('type_springer', 'Type', ['isbn' => 'ISBN', 'issn' => 'ISSN', 'doi' => 'DOI'])
            ->setDefaultValue('doi')
            ->setRequired('Type is required.');

        $this->addRadioList('data_springer', 'Data', []);
        ////->addRule(PublicationFormRules::SPRINGER_FETCH_DATA, "Please select fetched data and press DONE button again.", $parent);
        // $this->addHidden('again');
        $this->addSubmit('send', 'Import data');
    }

}
