<?php

namespace App\CrudComponents\Publisher;


class PublisherAddForm extends PublisherForm {

    /**
     * PublisherAddForm constructor.
     * @param \App\Model\Publisher $publisherModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
	    \App\Model\Publisher $publisherModel,
        \Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this['name']->addRule(function ($name) use ($publisherModel) {
			if ($publisherModel->findOneByName($name->value)) {
			    return false;
            } else {
			    return true;
            }
		}, "Name already exists.", $parent)
        ->setRequired('Name is required.');
	}

}