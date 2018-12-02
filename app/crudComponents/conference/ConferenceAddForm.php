<?php

namespace App\CrudComponents\Conference;


class ConferenceAddForm extends ConferenceForm {

	public function __construct(\App\Model\Conference $conferenceModel,
                                \Nette\ComponentModel\IContainer $parent = NULL,
                                string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this['name']->addRule(function ($field, $form) use ($conferenceModel) {
			if ($conferenceModel->getConferenceByName($field->value)) {
				return false;
			} else return true;
		}, "Conference with such name already exists.", $this);

		$this['abbreviation']->addRule(function ($field, $form) use ($conferenceModel) {
			if ($conferenceModel->getConferenceByAbbreviation($field->value)) {
				return false;
			} else return true;
		}, "Conference with such abbreviation already exists.", $this);

	}

}