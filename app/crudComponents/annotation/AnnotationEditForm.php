<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.5.2015
 * Time: 11:54
 */

namespace App\CrudComponents\Annotation;


class AnnotationEditForm extends AnnotationForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addHidden('id');
	}


}