<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 26.3.2015
 * Time: 20:17
 */

namespace App\CrudComponents\Conference;


class ConferenceAddForm extends ConferenceForm {

	public function __construct(\App\Model\Conference $conferenceModel, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this['name']->addRule(function($field, $form) use ($conferenceModel) {
			if($conferenceModel->getConferenceByName($field->value)){
				return false;
			} else return true;
		}, "Conference with such name already exists.", $this);

		$this['abbreviation']->addRule(function($field, $form) use ($conferenceModel) {
			if($conferenceModel->getConferenceByAbbreviation($field->value)) {
				return false;
			} else return true;
		}, "Conference with such abbreviation already exists.", $this);

	}


}