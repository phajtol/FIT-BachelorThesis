<?php

namespace App\CrudComponents\Reference;


class ReferenceEditForm extends \App\Forms\BaseForm {

    /**
     * ReferenceEditForm constructor.
     * @param int $publication_id
     * @param \App\Model\Publication $publicationModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(int $publication_id, \App\Model\Publication $publicationModel, \Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->addTextArea("text", "text");
		$this->addSubmit('save', 'Save');
        $this->addHidden("id");

        $this->action .= '#references';

        $this->setModal(true);
	}

}
