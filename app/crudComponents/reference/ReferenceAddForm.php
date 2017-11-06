<?php
namespace App\CrudComponents\Reference;


class ReferenceAddForm extends ReferenceForm {

	public function __construct($publication_id,\App\Model\Publication $publicationModel, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($publication_id,$publicationModel, $parent, $name);
	}
}