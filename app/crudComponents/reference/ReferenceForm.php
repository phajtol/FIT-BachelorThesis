<?php


namespace App\CrudComponents\Reference;


abstract class ReferenceForm extends \App\Forms\BaseForm {

    /**
     * ReferenceForm constructor.
     * @param int $publication_id
     * @param \App\Model\Publication $publicationModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(int $publication_id, \App\Model\Publication $publicationModel, \Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->addSelect("reference_id", "Referenced publication", $publicationModel->getPairsForReference($publication_id))
            ->setPrompt("-- please choose --")
            ->setRequired('Referenced publication is required.');

		$this->addSubmit('send', 'Add');

		$this->action .= '#references';

		$this->setModal(true);
	}

}
