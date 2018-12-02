<?php

namespace App\CrudComponents\Conference;

use App\Forms\BaseForm;


class ConferenceForm extends BaseForm implements \App\Forms\IMixtureForm {

    /**
     * ConferenceForm constructor.
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->addText('name', 'Name')
			->addRule($this::MAX_LENGTH, 'Name is way too long', 300)
			->setRequired('Name is required.');

		$this->addText('abbreviation', 'Abbreviation')
            ->setRequired("Abbreviation is required.")
            ->addRule($this::MAX_LENGTH, 'Abbreviation is way too long', 50);
                
		$this->addYear('first_year', 'First year');
		$this->addTextArea('description', 'Description', 6, 8)
            ->setRequired(false)
			->addRule($this::MAX_LENGTH, 'Description is way too long', 1000);

		$this->addText('acm_categories', 'ACM Categories')
            ->setRequired(false)
			->addRule(\PublicationFormRules::CATEGORIES, "Invalid category list");

		$this->addText('conference_categories', 'Conference custom categories')
            ->setRequired(false)
			->addRule(\PublicationFormRules::CATEGORIES, "Invalid category list");

		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');

		$this->setModal(true);
		$this->setAjax(true);

		$newRenderer = new \App\Helpers\CombinedFormRenderer();
		$newRenderer->addRenderer($this->getRenderer());

		$this->setLabelsSize(3);
	}

    /**
     *
     */
	public function removeConferencePart(): void
    {
		unset($this['acm_categories']);
		unset($this['conference_categories']);
	}

    /**
     *
     */
	public function removePublicationPart(): void
    {
        //wtf
	}

}