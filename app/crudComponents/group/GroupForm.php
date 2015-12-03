<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.5.2015
 * Time: 22:46
 */

namespace App\CrudComponents\Group;


class GroupForm extends \App\Forms\BaseForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {

		parent::__construct($parent, $name);

		$this->addText('name', 'Name')->addRule($this::MAX_LENGTH, 'Name is way too long', 250)->setRequired('Name is required.');


		$this->addSubmit('send', 'Done');

		$this->setAjax(true);

		$this->setModal(true);

	}


}