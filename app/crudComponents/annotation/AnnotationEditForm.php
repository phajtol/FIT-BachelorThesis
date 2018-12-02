<?php

namespace App\CrudComponents\Annotation;


class AnnotationEditForm extends AnnotationForm {

    /**
     * AnnotationEditForm constructor.
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->addHidden('id');
	}


}