<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 15.3.2015
 * Time: 20:54
 */

namespace App\Components;


class StaticContentComponent extends \Nette\Application\UI\Control {

	protected $templateFile;

	public function __construct($templateFile, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
        parent::__construct();
        $parent->addComponent($this, $name);
		$this->templateFile = $templateFile;
	}

	public function render(){
		$this->template->setFile($this->templateFile);
		$this->template->render();
	}


}