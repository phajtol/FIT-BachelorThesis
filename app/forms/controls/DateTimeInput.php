<?php

namespace App\Forms\Controls;

use Nette\Utils\DateTime;

class DateTimeInput extends \Nette\Forms\Controls\TextInput {

	public function __construct(string $label = NULL) {
		parent::__construct($label, null);

		$this->getControlPrototype()->addClass('datetime');
		$this->addRule(\Nette\Application\UI\Form::PATTERN, sprintf('Datum a čas u pole "%s" je třeba vyplnit ve formátu d.m.yyyy hh:mm', $label), '(|([1-9]|[12]\d|3[01])\.([1-9]|[1][012]).(19\d\d|20\d\d) ([01][0-9]|2[0-3]):[0-5][0-9])');
	}

	public function getValueTransformed() {
		return new DateTime(parent::getValue());
	}

	public function setValue($value) {
		if(is_object($value) && $value instanceof DateTime) {
			parent::setValue($value->format('j.n.Y H:i'));
		} else {
			parent::setValue($value);
		}
	}

}