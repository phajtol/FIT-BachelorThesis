<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 14.3.2015
 * Time: 21:16
 */

namespace App\Forms;


class BaseForm extends \Nette\Application\UI\Form {

	protected $isModal = false;
	protected $isAjax = false;

	protected $labelsSize = 2;


	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->addProtection('Security token has expired, please submit the form again.');
	}

	/**
	 * @return int
	 */
	public function getLabelsSize() {
		return $this->labelsSize;
	}

	/**
	 * @param int $labelsSize
	 */
	public function setLabelsSize($labelsSize) {
		$this->labelsSize = $labelsSize;
	}


	public function addSubmit($name, $caption = NULL)
	{
		$control = parent::addSubmit($name, $caption);
		$control->getControlPrototype()->addClass('btn-primary');
		return $control;
	}

	public function addCloseButton($name, $caption = NULL)
	{
		$control = parent::addButton($name, $caption);
		$control->setOmitted(true)->getControlPrototype()->addClass('btn-default close-button');
		return $control;
	}

	public function addDate($name, $caption = NULL) {
		return $this[$name] = new \App\Forms\Controls\DateInput($caption);
	}

	public function addDateTime($name, $caption = NULL) {
		return $this[$name] = new \App\Forms\Controls\DateTimeInput($caption);
	}

	public function addYear($name, $caption = NULL) {
		return $this[$name] = new \App\Forms\Controls\YearInput($caption);
	}

	public function addMonth($name, $caption = NULL) {
		return $this[$name] = new \App\Forms\Controls\MonthInput($caption);
	}


	public function addButton($name, $caption = NULL)
	{
		$control = parent::addButton($name, $caption);
		$control->getControlPrototype()->addClass('btn-default');
		return $control;
	}

	public function addMultiSelect($name, $label = NULL, array $items = NULL, $size = NULL) {
		$c = parent::addMultiSelect($name, $label, $items, $size);
		$c->getControlPrototype()->addClass('select-multiple');
		return $c;
	}

	public function setModal($modal = true){
		$this->isModal = $modal;
	}

	public function setAjax($ajax = true) {
		$this->isAjax = $ajax;
	}

	public function clearValues() {
		$this->setValues(array(), true);
	}

	/**
	 * Retrieves transformed values from form.
	 * Controls can implement getValueTransformed method to retrieve transformed data (ex. DateTime object)
	 * @param bool $asArray
	 * @return array|\Nette\Utils\ArrayHash
	 */
	public function getValuesTransformed($asArray = FALSE) {
		$values = $asArray ? array() : new \Nette\Utils\ArrayHash;
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

	public function getValues($asArray = FALSE) {
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
	 */
	public function render(){

		// modal behaviour
		if($this->isModal) {
			$mainGroup = $this->addGroup()->setOption('container', 'div class=modal-body');;
			$buttonGroup = $this->addGroup()->setOption('container', 'div class=modal-footer');;

			// divide controls into groups
			foreach($this->getControls() as $control){
				/** @var $control \Nette\Forms\Controls\BaseControl */
				if($control instanceof \Nette\Forms\Controls\Button) {
					$buttonGroup->add($control);
				} else $mainGroup->add($control);
			}
		}

		// ajax behaviour
		if($this->isAjax) {
			$this->getElementPrototype()->class('ajax');
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
		$this->getElementPrototype()->class('form-horizontal');

		foreach ($this->getControls() as $control) {

			/** @var $control \Nette\Forms\Controls\BaseControl */

			if ($control instanceof \Nette\Forms\Controls\Button) {
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