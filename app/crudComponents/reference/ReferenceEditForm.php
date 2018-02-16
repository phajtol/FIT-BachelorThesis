<?php


namespace App\CrudComponents\Reference;


class ReferenceEditForm extends \App\Forms\BaseForm {

	public function __construct($publication_id,\App\Model\Publication $publicationModel, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addTextArea("text", "text");
		$this->addSubmit('save', 'Save');
 		$this->action .= '#references';

		$this->setModal(true);
    $this->addHidden("id");
	}

}
