<?php

namespace App\Forms;


use App\Forms\Controls\DateInput;
use App\Forms\Controls\DateTimeInput;
use App\Forms\Controls\MonthInput;
use App\Forms\Controls\YearInput;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\SubmitButton;

class BaseForm extends \Nette\Application\UI\Form {

    /** @var bool */
	protected $isModal = false;

	/** @var bool */
	protected $isAjax = false;

	/** @var int */
	protected $labelsSize = 2;


    /**
     * BaseForm constructor.
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);
		$this->addProtection('Security token has expired, please submit the form again.');
	}

	/**
	 * @return int
	 */
	public function getLabelsSize(): int
    {
		return $this->labelsSize;
	}

	/**
	 * @param int $labelsSize
	 */
	public function setLabelsSize(int $labelsSize): void
    {
		$this->labelsSize = $labelsSize;
	}

    /**
     * @param string $name
     * @param string|NULL $caption
     * @return \Nette\Forms\Controls\SubmitButton
     */
    public function addSubmit($name, $caption = NULL)
	{
		$control = parent::addSubmit($name, $caption);
		$control->getControlPrototype()->addClass('btn-primary');
		return $control;
	}

    /**
     * @param string$name
     * @param string|NULL $caption
     * @return Button
     */
	public function addCloseButton($name, $caption = NULL)
	{
		$control = parent::addButton($name, $caption);
		$control->setOmitted(true)->getControlPrototype()->addClass('btn-default close-button');
		return $control;
	}

    /**
     * @param string $name
     * @param string|NULL $caption
     * @return DateInput
     */
	public function addDate($name, $caption = NULL)
    {
		return $this[$name] = new DateInput($caption);
	}

    /**
     * @param string $name
     * @param string|NULL $caption
     * @return DateTimeInput
     */
	public function addDateTime($name, $caption = NULL)
    {
		return $this[$name] = new DateTimeInput($caption);
	}

    /**
     * @param string $name
     * @param string|NULL $caption
     * @return YearInput
     */
	public function addYear($name, $caption = NULL)
    {
		return $this[$name] = new YearInput($caption);
	}

    /**
     * @param string $name
     * @param string|NULL $caption
     * @return MonthInput
     */
	public function addMonth($name, $caption = NULL)
    {
		return $this[$name] = new MonthInput($caption);
	}

    /**
     * @param string $name
     * @param string|NULL $caption
     * @return Button
     */
	public function addButton($name, $caption = NULL)
	{
		$control = parent::addButton($name, $caption);
		$control->getControlPrototype()->addClass('btn-default');
		return $control;
	}

    /**
     * @param string $name
     * @param string|NULL $label
     * @param array|NULL $items
     * @param int|NULL $size
     * @return MultiSelectBox
     */
	public function addMultiSelect($name, $label = NULL, array $items = NULL, $size = NULL)
    {
		$c = parent::addMultiSelect($name, $label, $items, $size);
		$c->getControlPrototype()->addClass('select-multiple');
		return $c;
	}

    /**
     * @param bool $modal
     */
	public function setModal(bool $modal = true): void
    {
		$this->isModal = $modal;
	}

    /**
     * @param bool $ajax
     */
	public function setAjax(bool $ajax = true): void
    {
		$this->isAjax = $ajax;
	}

    /**
     *
     */
	public function clearValues(): void
    {
		$this->setValues(array(), true);
	}

	/**
	 * Retrieves transformed values from form.
	 * Controls can implement getValueTransformed method to retrieve transformed data (ex. DateTime object)
	 * @param bool $asArray
	 * @return array|\Nette\Utils\ArrayHash
	 */
	public function getValuesTransformed(bool $asArray = FALSE)
    {
		$values = $asArray ? [] : new \Nette\Utils\ArrayHash;

		foreach ($this->getComponents() as $name => $control) {
			if( substr($name, 0, 2) == '__' ) continue;

			if ($control instanceof \Nette\Forms\IControl && !$control->isOmitted()) {
				if(method_exists($control, 'getValueTransformed')) {
					$values[$name] = $control->getValueTransformed();
 				} else {
					$values[$name] = $control->getValue();
				}
			} elseif ($control instanceof \Nette\Forms\Container) {
				if(method_exists($control, 'getValuesTransformed')) {
					$values[$name] = $control->getValuesTransformed($asArray);
				} else {
					$values[$name] = $control->getValues($asArray);
				}
			}
		}

		return $values;
	}

    /**
     * @param bool $asArray
     * @return array|\Nette\Utils\ArrayHash
     */
	public function getValues($asArray = FALSE)
    {
		$values = parent::getValues($asArray);

		foreach($values as $k => $v) {
			if(substr($k, 0, 2) == '__') unset($values[$k]);
		}
		return $values;
	}

	public function addConfirmableRule($fieldName, $validatorCallback, $message) {
		if(!isset($this['__again'])) {
			$this->addHidden('__again')->setDefaultValue(0);
		}
		$this[$fieldName]->addRule(function() use ($validatorCallback) {
			if($this['__again']->getValue() == 1) return true;
			else {
				$this['__again']->setValue(1);
				return $validatorCallback();
			}
		}, $message . " Please submit the form AGAIN to confirm the action.");
	}

    /**
     * for trivial forms that don't require custom rendering
     * @param array $args
     */
	public function render(...$args){

		// modal behaviour
		if($this->isModal) {
			$mainGroup = $this->addGroup()->setOption('container', 'div class=modal-body');;
			$buttonGroup = $this->addGroup()->setOption('container', 'div class=modal-footer');;

			// divide controls into groups
			foreach($this->getControls() as $control){
				/** @var $control \Nette\Forms\Controls\BaseControl */
				if($control instanceof Button) {
					$buttonGroup->add($control);
				} else $mainGroup->add($control);
			}
		}

		// ajax behaviour
		if($this->isAjax) {
			$this->getElementPrototype()->addClass('ajax');
		}


		// setup for twitter bootstrap
		$renderer = $this->getRenderer();
		$renderer->wrappers['controls']['container'] = NULL;
		//$renderer->wrappers['group']['container'] = 'div';
		$renderer->wrappers['pair']['container'] = 'div class=form-group';
		$renderer->wrappers['pair']['.error'] = 'has-error';
		$renderer->wrappers['control']['container'] = 'div class=col-sm-8';
		$renderer->wrappers['label']['container'] = 'div class="col-sm-' . $this->labelsSize . ' control-label"';
		$renderer->wrappers['control']['description'] = 'span class=help-block';
		$renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
		$renderer->wrappers['control']['.button'] = 'div';
		$renderer->wrappers['checkbox']['container'] = 'div class="checkbox"';
		// make form and controls compatible with Twitter Bootstrap
		$this->getElementPrototype()->addClass('form-horizontal');

		foreach ($this->getControls() as $control) {

			/** @var $control \Nette\Forms\Controls\BaseControl */

			if ($control instanceof Button) {
				$control->getControlPrototype()->addClass('btn');
			} elseif ($control instanceof \Nette\Forms\Controls\TextBase || $control instanceof \Nette\Forms\Controls\SelectBox || $control instanceof \Nette\Forms\Controls\MultiSelectBox) {
				$control->getControlPrototype()->addClass('form-control');
			} elseif ($control instanceof \Nette\Forms\Controls\Checkbox || $control instanceof \Nette\Forms\Controls\Checkbox || $control instanceof \Nette\Forms\Controls\RadioList || $control instanceof \Nette\Forms\Controls\CheckboxList) {
				$control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
			}
		}

		parent::render();
	}


}