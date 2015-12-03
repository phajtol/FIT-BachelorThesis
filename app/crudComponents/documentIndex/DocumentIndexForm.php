<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 15.4.2015
 * Time: 16:44
 */

namespace App\CrudComponents\DocumentIndex;


class DocumentIndexForm extends \App\Forms\BaseForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addText('name', 'Name')->setRequired('Name is required.');
		$this->addText('web', 'Web')->addRule(\Nette\Application\UI\Form::URL, "The web address must be a valid url");

		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');

		$this->setModal(true);
		$this->setAjax(true);
	}


}