<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 2.4.2015
 * Time: 16:32
 */

namespace App\Components;


class BaseControl extends \Nette\Application\UI\Control {

	protected $defaultTemplateVars = [];

	protected function addDefaultTemplateVar($var, $value) {
		$this->defaultTemplateVars = array_merge($this->defaultTemplateVars, array($var => $value));
	}

	protected function addDefaultTemplateVars($array) {
		$this->defaultTemplateVars = array_merge($this->defaultTemplateVars, $array);
	}

	public function render() {
		foreach($this->defaultTemplateVars as $k => $v) {
			if(!isset($this->template->$k)) {
				$this->template->$k = $v;
			}
		}
	}

}