<?php

namespace App\Forms\Controls;

use Nette\Forms\Controls\TextInput;
use Nette\Utils\DateTime;


class DateInput extends TextInput {

    /**
     * DateInput constructor.
     * @param string|NULL $label
     */
	public function __construct(string $label = NULL)
    {
		parent::__construct($label, null);

		$this->getControlPrototype()->addClass('date');

		$this->addCondition(\Nette\Forms\Form::FILLED)->addRule(\Nette\Forms\Form::PATTERN, sprintf('The field "%s" must be filled with a valid date in format d.m.yyyy', $label), '([1-9]|[12]\d|3[01])\.([1-9]|[1][012])\.(19\d\d|20\d\d)');
	}

    /**
     * @return DateTime|null
     */
	public function getValueTransformed(): ?DateTime
    {
		$val = parent::getValue();
		if (!$val) {
		    return null;
        }

		return new DateTime($val);
	}

    /**
     * @param $value
     * @return $this|\Nette\Forms\Controls\TextInput
     */
	public function setValue($value): TextInput
    {
		if (is_object($value) && $value instanceof DateTime) {
			parent::setValue($value->format('j.n.Y'));
		} else {
			parent::setValue($value);
		}

		return $this;
	}

}