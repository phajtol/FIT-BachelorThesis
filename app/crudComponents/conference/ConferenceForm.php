<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 26.3.2015
 * Time: 20:15
 */

namespace App\CrudComponents\Conference;


use App\Forms\BaseForm;

class ConferenceForm extends BaseForm implements \App\Forms\IMixtureForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addText('name', 'Name')
			->addRule($this::MAX_LENGTH, 'Name is way too long', 300)
			->setRequired('Name is required.');

		$this->addText('abbreviation', 'Abbreviation')->addRule($this::MAX_LENGTH, 'Abbreviation is way too long', 50);
		$this->addYear('first_year', 'First year');
		$this->addTextArea('description', 'Description', 6, 8)
			->addRule($this::MAX_LENGTH, 'Description is way too long', 1000);

		$this->addText('acm_categories', 'ACM Categories')
			->addRule(\PublicationFormRules::CATEGORIES, "Invalid category list");
		$this->addText('conference_categories', 'Conference custom categories')
			->addRule(\PublicationFormRules::CATEGORIES, "Invalid category list");

		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');

		$this->setModal(true);
		$this->setAjax(true);

		$newRenderer = new \App\Helpers\CombinedFormRenderer();
		$newRenderer->addRenderer($this->getRenderer());

	}

	public function removeConferencePart() {
		unset($this['acm_categories']);
		unset($this['conference_categories']);
	}

	public function removePublicationPart() {

	}


}