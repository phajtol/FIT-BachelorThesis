<?php

use Nette\Application\UI;
use Nette\ComponentModel\IContainer;

class PublicationAddNewGeneralSettingsForm extends UI\Form {

    /**
     * PublicationAddNewGeneralSettingsForm constructor.
     * @param IContainer|NULL $parent
     * @param string|NULL $name
     */
    public function __construct(IContainer $parent = NULL, string $name = NULL)
    {
        parent::__construct($parent, $name);

        $items = [];
        $counter = 0;
        for ($i = 0; $i < 20; $i++) {
            $counter += 5;
            $items[$counter] = $counter;
        }

        $this->addSelect('pagination', 'Items per page', $items);

        $this->addText('spring_token', 'Spring token')
            ->addRule($this::MAX_LENGTH, 'Spring token is way too long', 250)
            ->setRequired('Spring token is required.');

        $this->addText('deadline_notification_advance', 'Conference deadlines notification advance [days]', $items)
            ->setRequired(true)
            ->addRule(self::INTEGER, 'The deadline advance must be a number')
            ->addRule(self::MIN, 'The deadline advance must be min. 1 day', 1)
            ->addRule(self::MAX, 'The deadline must be max. 90 days', 90);


        //  $this->addText('pagination', 'Items per page');
        $this->addHidden('id');

        $this->addSubmit('send', 'Done');

        $this->getElementPrototype()->class('ajax');
        $this->addProtection('Security token has expired, please submit the form again.');
    }

}
