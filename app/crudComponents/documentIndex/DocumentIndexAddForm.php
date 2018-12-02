<?php

namespace App\CrudComponents\DocumentIndex;


class DocumentIndexAddForm extends DocumentIndexForm  {

    /**
     * DocumentIndexAddForm constructor.
     * @param \App\Model\DocumentIndex $documentIndexModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\App\Model\DocumentIndex $documentIndexModel, \Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this['name']->addRule(function ($nameField) use ($documentIndexModel) {
			if($documentIndexModel->findOneByName($nameField->getValue())){
				return false;
			} else {
			    return true;
            }
		}, "Document index with such name already exists.", $this);
	}

}