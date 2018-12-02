<?php

namespace App\Forms\Controls;


class YearInput extends \Nette\Forms\Controls\TextInput {

    /**
     * YearInput constructor.
     * @param string|NULL $label
     */
	public function __construct(string $label = NULL)
    {
		parent::__construct($label, null);

		$this->getControlPrototype()->addClass('year');

		$this->addCondition(\Nette\Forms\Form::FILLED)->addRule(\Nette\Forms\Form::PATTERN, sprintf('The field "%s" must be filled with a valid date in format yyyy', $label), '(19\d\d|20\d\d)');
	}

    /**
     * @return mixed|null
     */
	public function getValueTransformed()
    {
		$val = parent::getValue();
		if(!$val) return null;
		return $val;
	}

}