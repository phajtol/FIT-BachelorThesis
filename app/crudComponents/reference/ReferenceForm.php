<?php


namespace App\CrudComponents\Reference;


abstract class ReferenceForm extends \App\Forms\BaseForm {

	public function __construct($publication_id,\App\Model\Publication $publicationModel, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addSelect("reference_id", "Referenced publication", $publicationModel->getPairsForReference($publication_id))
                        ->setPrompt("-- please choose --")
                        ->setRequired('Referenced publication is required.');
		$this->addSubmit('send', 'Add');
 		$this->action .= '#references';

		$this->setModal(true);
	}

}
