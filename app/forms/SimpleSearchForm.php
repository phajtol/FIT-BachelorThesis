<?php

namespace App\Forms;

use \Nette\Application\UI;


class SimpleSearchForm {

    use \Nette\SmartObject;

    /**
     * @return UI\Form
     */
    public function create(): UI\Form
    {
        $form = new UI\Form;

        $form->addText('title', 'Keywords')
            ->setRequired(true)
            ->addRule($form::MAX_LENGTH, 'Title is way too long', 100);

        $form->addSubmit('send', 'Search');

        return $form;
    }

}
