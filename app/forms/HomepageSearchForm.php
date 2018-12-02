<?php

use Nette\Application\UI;

class HomepageSearchForm {

    use \Nette\SmartObject;

    /** @var \Nette\Database\Connection */
    private $database;


    /**
     * HomepageSearchForm constructor.
     * @param \Nette\Database\Connection $database
     */
    public function __construct(Nette\Database\Connection $database)
    {
        $this->database = $database;
    }

    /**
     * @param $data
     * @return UI\Form
     */
    public function create($data): UI\Form
    {
        $form = new UI\Form;

        $form->addText('keywords', 'Keywords')
          ->setRequired(false)
          ->addRule($form::MAX_LENGTH, 'Keywords is way too long', 100);

        $form->addRadioList('operator', 'Search operator', ['OR' => 'OR', 'AND' => 'AND'])
            ->setDefaultValue('OR');

        $form->addText('categories');

        $form->addRadioList('searchtype', 'Search type', ['fulltext' => 'Fulltext search', 'authors' => 'Authors / Publication search'])
            ->setDefaultValue('fulltext');

        $form->addCheckbox('starredpubs', 'Search only in starred publications');

        $form->addCheckbox('advanced', 'Enable advanced search')
            ->setDefaultValue(FALSE);

        $form->addSubmit('send', 'Search');

        $form->setDefaults($data);

        return $form;
    }

}
