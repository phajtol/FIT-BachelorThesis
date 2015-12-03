<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 4.5.2015
 * Time: 12:43
 */

namespace App\Forms\Controls;


class MonthInput extends \Nette\Forms\Controls\TextInput {

	public function __construct($label = NULL) {
		parent::__construct($label, null);

		$this->getControlPrototype()->addClass('month');

		$this->addCondition(\Nette\Forms\Form::FILLED)->addRule(\Nette\Forms\Form::PATTERN, sprintf('The field "%s" must be filled with a valid date in format mm/yyyy', $label), '(0[1-9]|[1][012])\/(19\d\d|20\d\d)');
	}

	public function getValueTransformed() {
		$val = parent::getValue();
		if(!$val) return null;
		list($month, $year) = explode("/", $val);
		return new \Nette\Utils\DateTime($year . "-" . $month . "-1");
	}


	public function setValue($value) {
		if(is_object($value) && $value instanceof \Nette\Utils\DateTime) {
			parent::setValue($value->format('m/Y'));
		} else {
			parent::setValue($value);
		}
		return $this;
	}
}