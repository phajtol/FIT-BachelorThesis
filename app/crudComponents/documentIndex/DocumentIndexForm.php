<?php

namespace App\CrudComponents\DocumentIndex;


class DocumentIndexForm extends \App\Forms\BaseForm {

    /**
     * DocumentIndexForm constructor.
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->addText('name', 'Name')
            ->setRequired('Name is required.');

		$this->addText('web', 'Web')
            ->addRule(\Nette\Application\UI\Form::URL, "The web address must be a valid url")
            ->setRequired(false);

		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');

		$this->setModal(true);
		$this->setAjax(true);
	}

}