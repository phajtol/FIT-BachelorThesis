<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 15.3.2015
 * Time: 20:35
 */

namespace App\CrudComponents\Publisher;


abstract class PublisherForm extends \App\Forms\BaseForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		$this->setModal(true);
		$this->setAjax(true);

		$this->addText('name', 'Name')
			->addRule($this::MAX_LENGTH, 'Name is way too long', 500)
			->setRequired('Name is required.');

		$this->addText('address', 'Address')->addRule($this::MAX_LENGTH, 'Address is way too long', 500);

		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');

	}

}