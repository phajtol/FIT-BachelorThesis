<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 15.4.2015
 * Time: 16:44
 */

namespace App\CrudComponents\DocumentIndex;


class DocumentIndexAddForm extends DocumentIndexForm  {

	public function __construct(\App\Model\DocumentIndex $documentIndexModel, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this['name']->addRule(function($nameField) use ($documentIndexModel) {
			if($documentIndexModel->findOneByName($nameField->getValue())){
				return false;
			} else return true;
		}, "Document index with such name already exists.", $this);
	}


}