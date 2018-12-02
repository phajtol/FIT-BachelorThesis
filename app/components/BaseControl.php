<?php

namespace App\Components;


class BaseControl extends \Nette\Application\UI\Control {

    /** @var array */
	protected $defaultTemplateVars = [];

    /**
     * @param $var
     * @param $value
     */
	protected function addDefaultTemplateVar($var, $value): void
    {
		$this->defaultTemplateVars = array_merge($this->defaultTemplateVars, [$var => $value]);
	}

    /**
     * @param array $array
     */
	protected function addDefaultTemplateVars(array $array): void
    {
		$this->defaultTemplateVars = array_merge($this->defaultTemplateVars, $array);
	}

    /**
     *
     */
	public function render(): void
    {
		foreach($this->defaultTemplateVars as $k => $v) {
			if (!isset($this->template->$k)) {
				$this->template->$k = $v;
			}
		}
	}

}