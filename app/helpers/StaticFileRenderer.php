<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 31.3.2015
 * Time: 19:21
 */

namespace App\Helpers;


class StaticFileRenderer implements \Nette\Forms\IFormRenderer {

	protected $file;

	function __construct($file) {
		if(!$file || !file_exists($file)) throw new \Exception(sprintf("Given template file '%s' does not exist", $file));
		$this->file = $file;
	}

	/**
	 * Provides complete form rendering.
	 * @return string
	 */
	function render(\Nette\Forms\Form $form) {
		echo file_get_contents($file);
	}


}