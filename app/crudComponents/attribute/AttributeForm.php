<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 27.3.2015
 * Time: 23:05
 */

namespace App\CrudComponents\Attribute;


class AttributeForm extends \App\Forms\BaseForm {


	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addText('name', 'Name')
			->addRule($this::PATTERN, 'Name must start with character _', '[_].*')
			->addRule($this::MIN_LENGTH, 'Name is way too short', 2)
			->addRule($this::MAX_LENGTH, 'Name is way too long', 50)
			->setRequired('Name is required.');

		$this->addTextArea('description', 'Description', 6, 8)
			->setRequired('Description is required.')
			->addRule($this::MAX_LENGTH, 'Description is way too long', 200);

		$this->addRadioList('confirmed', 'Visibility', array('0' => 'Private', '1' => 'Global'))->setDefaultValue('0')->setRequired('Visibility is required.');

		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');

		$this->setModal(true);
		$this->setAjax(true);
	}


}