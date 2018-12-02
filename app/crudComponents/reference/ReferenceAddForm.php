<?php
namespace App\CrudComponents\Reference;

use App\Model\Publication;
use Nette\ComponentModel\IContainer;


class ReferenceAddForm extends ReferenceForm {

    /**
     * ReferenceAddForm constructor.
     * @param int $publication_id
     * @param Publication $publicationModel
     * @param IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(int $publication_id, Publication $publicationModel, IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($publication_id,$publicationModel, $parent, $name);
	}

}