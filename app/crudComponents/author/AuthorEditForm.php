<?php

namespace App\CrudComponents\Author;


class AuthorEditForm  extends AuthorForm {

    /**
     * AuthorEditForm constructor.
     * @param \App\Model\Submitter $submitterModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\App\Model\Submitter $submitterModel,\Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($submitterModel,$parent, $name);

		$this->addHidden('id');
	}


}