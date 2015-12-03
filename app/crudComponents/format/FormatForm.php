<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 15.4.2015
 * Time: 16:44
 */

namespace App\CrudComponents\Format;


class FormatForm extends \App\Forms\BaseForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addText('name', 'Name')->setRequired('Name is required.')->addRule($this::MAX_LENGTH, 'Name is way too long', 50);

		$this->addTextArea('content', 'Source code', 6, 8)->addRule($this::MAX_LENGTH, 'Source code is way too long', 20000)->setRequired('Source code is required.');

		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');

		$this->setModal(true);
		$this->setAjax(true);
	}


}
?>