<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 31.3.2015
 * Time: 19:18
 */

namespace App\Helpers;


class CombinedFormRenderer implements \Nette\Forms\IFormRenderer {

	/**
	 * @var \Nette\Forms\IFormRenderer[]
	 */
	protected $renderers = [];

	public function addRenderer(\Nette\Forms\IFormRenderer $renderer){
		$this->renderers[] = $renderer;

		return count($this->renderers) - 1;
	}

	public function removeRenderer($index){
		if(isset($this->renderers[$index])) unset($this->renderers[$index]);
	}

	/**
	 * Provides complete form rendering.
	 * @return string
	 */
	function render(\Nette\Forms\Form $form) {
		foreach($this->renderers as $renderer) {
			$renderer->render($form);
		}
	}

}