<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.5.2015
 * Time: 11:54
 */

namespace App\CrudComponents\PublicationTag;


class PublicationTagForm extends \App\Forms\BaseForm {

	public function __construct(\App\Model\Tag $tagModel, $loggedUser, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

    $this->addSelect("tag_id", "Tag", $tagModel->getPairs($loggedUser->id));

		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');

		$this->action .= '#tags';

		$this->setModal(true);
		$this->setAjax(true);

	}

}
