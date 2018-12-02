<?php

namespace App\CrudComponents\Tag;


class TagForm extends \App\Forms\BaseForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->addText('name', 'Name')
            ->setRequired($this::FILLED, 'Please fill tag name.')
            ->addRule($this::MAX_LENGTH, 'Tag name is way too long', 100);

		$this->addRadioList('global_scope', 'Visibility', ['0' => 'Private', '1' => 'Global'])
            ->setDefaultValue('0');

        $this->addHidden('id');

        $this->addCloseButton('cancel', 'Cancel');
        $this->addSubmit('send', 'Done');

        $this->action .= '#tags';

        $this->setModal(true);
        $this->setAjax(true);
	}

}
