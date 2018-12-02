<?php

namespace App\CrudComponents\PublicationTag;


class PublicationTagForm extends \App\Forms\BaseForm {

    /**
     * PublicationTagForm constructor.
     * @param \App\Model\Tag $tagModel
     * @param \Nette\Security\User $loggedUser
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(
	    \App\Model\Tag $tagModel,
        \Nette\Security\User $loggedUser,
        \Nette\ComponentModel\IContainer $parent = NULL,
        string $name = NULL)
    {
		parent::__construct($parent, $name);

        $this->addSelect('tag_id', 'Tag', $tagModel->getPairs($loggedUser->id));

		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');

		$this->action .= '#tags';

		$this->setModal(true);
		$this->setAjax(true);
	}

}
