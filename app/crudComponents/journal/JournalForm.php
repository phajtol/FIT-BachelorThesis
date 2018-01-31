<?php


namespace App\CrudComponents\Journal;


class JournalForm extends \App\Forms\BaseForm {

	protected $journalModel;

	public function __construct(\App\Model\Journal $journalModel, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->journalModel = $journalModel;

		$this->addText('name', 'Name')
			->addRule($this::MAX_LENGTH, 'Name is way too long', 500)
			->setRequired('Name is required.');

		$this->addText('abbreviation', 'Abbreviation')
			->addRule($this::MAX_LENGTH, 'Abbreviation is way too long', 100);

		$this->addHidden('isbn_count');
		$cont = $this->addContainer("isbn");

		$this->addIsbn();

		$this->addText('doi', 'DOI')
			->addRule($this::MAX_LENGTH, 'DOI is way too long', 100);


		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');


		$this->addHidden('id');

		$this->setModal(true);
		$this->setAjax(true);

		$id = $this['id']->getValue();
		if (empty($id)) {
			$this['name']->addRule(function($nameField) use ($journalModel) {
				if($journalModel->findOneByName($nameField->getValue())){
					return false;
				} else return true;
			}, "Journal already exists.", $this);
		}
	}

	public function setIsbnCount($count) {
		$this['isbn_count']->setValue($count);
	}

	public function getIsbnCount() {
		return $this['isbn_count']->getValue();
	}

	public function addIsbn() {
		$count = $this['isbn_count']->getValue();
		$cont = $this['isbn'];

		for ($i=0;$i<$count;$i++) {
			if (!empty($cont[$i])) {
				continue;
			}
			$cont2 = $cont->addContainer($i);
			$cont2->addText("isbn", "ISBN/ISSN")
				->setRequired(false);
			$cont2->addSelect("type", "Typ", ["ISBN" => "ISBN", "ISSN" => "ISSN"]);
			if (empty($cont2['type']->getValue())) {
				$cont2['type']->setValue("ISBN");
			}

			$cont2->addText("note", "Note")
				->setRequired(false);
		}
	}

}
