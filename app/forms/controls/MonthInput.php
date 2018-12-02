<?php

namespace App\Forms\Controls;

use Nette\Utils\DateTime;


class MonthInput extends \Nette\Forms\Controls\TextInput {

	// month OR only year implementation - see https://github.com/Eonasdan/bootstrap-datetimepicker/pull/666

    /**
     * MonthInput constructor.
     * @param string|NULL $label
     */
	public function __construct(string $label = NULL)
    {
		parent::__construct($label, null);

		$this->getControlPrototype()->addClass('month');

		$this->addCondition(\Nette\Forms\Form::FILLED)->addRule(\Nette\Forms\Form::PATTERN, sprintf('The field "%s" must be filled with a valid date in format mm/yyyy', $label), '(0[1-9]|[1][012])\/(19\d\d|20\d\d)');
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

		list($month, $year) = explode("/", $val);
		return new DateTime($year . "-" . $month . "-1");
	}

    /**
     * @param $value
     * @return MonthInput
     */
	public function setValue($value): MonthInput
    {
		if(is_object($value) && $value instanceof DateTime) {
			parent::setValue($value->format('m/Y'));
		} else {
			parent::setValue($value);
		}
		return $this;
	}

}