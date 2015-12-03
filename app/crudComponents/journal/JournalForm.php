<?php


namespace App\CrudComponents\Journal;


abstract class JournalForm extends \App\Forms\BaseForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addText('name', 'Name')
			->addRule($this::MAX_LENGTH, 'Name is way too long', 500)
			->setRequired('Name is required.');

		$this->addText('abbreviation', 'Abbreviation')
			->addRule($this::MAX_LENGTH, 'Abbreviation is way too long', 100);

		$this->addText('issn', 'ISSN')
			->addCondition($this::FILLED)
			->addRule($this::PATTERN, 'ISSN is not in correct form.', '[0-9]{4}-([0-9]{4}|[0-9]{3}X)');

		$this->addText('doi', 'DOI')
			->addRule($this::MAX_LENGTH, 'DOI is way too long', 100);


		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');

		$this->setModal(true);
		$this->setAjax(true);
	}

}