<?php

namespace App\Components;


class StaticContentComponent extends \Nette\Application\UI\Control {

    /** @var string */
	protected $templateFile;

    /**
     * StaticContentComponent constructor.
     * @param string $templateFile
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(string $templateFile, \Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
	{
        parent::__construct();
        $parent->addComponent($this, $name);
		$this->templateFile = $templateFile;
	}

    /**
     *
     */
	public function render(): void
    {
		$this->template->setFile($this->templateFile);
		$this->template->render();
	}


}