<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 19.3.2015
 * Time: 1:02
 */

namespace App\CrudComponents\Publisher;


class PublisherAddForm extends PublisherForm {

	public function __construct(\App\Model\Publisher $publisherModel, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this["name"]->addRule(function($name) use ($publisherModel){
			if($publisherModel->findOneByName($name->value)) return false; else return true;
		}, "Name already exists.", $parent);
	}

}