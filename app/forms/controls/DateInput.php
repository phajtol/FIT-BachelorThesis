<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 18.3.2015
 * Time: 14:18
 */

namespace App\Forms\Controls;

use Nette\Utils\DateTime;

class DateInput extends \Nette\Forms\Controls\TextInput {

	public function __construct($label = NULL) {
		parent::__construct($label, null);

		$this->getControlPrototype()->addClass('date');

		$this->addCondition(\Nette\Forms\Form::FILLED)->addRule(\Nette\Forms\Form::PATTERN, sprintf('The field "%s" must be filled with a valid date in format d.m.yyyy', $label), '([1-9]|[12]\d|3[01])\.([1-9]|[1][012])\.(19\d\d|20\d\d)');
	}

	public function getValueTransformed() {
		$val = parent::getValue();
		if(!$val) return null;
		return new DateTime($val);
	}


	public function setValue($value) {
		if(is_object($value) && $value instanceof DateTime) {
			parent::setValue($value->format('j.n.Y'));
		} else {
			parent::setValue($value);
		}
		return $this;
	}

}