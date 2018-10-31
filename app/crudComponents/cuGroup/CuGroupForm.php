<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.4.2015
 * Time: 2:47
 */

namespace App\CrudComponents\CuGroup;


abstract class CuGroupForm extends \App\Forms\BaseForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
        parent::__construct();
        if ($parent) {
            $parent->addComponent($this, $name);
        }

		$this->addText('name', 'Name')->setRequired('Name is required.');

		$this->addText('conference_categories', 'Conference categories')
			->addRule(\PublicationFormRules::CATEGORIES, "Invalid category list")
            ->setRequired('Categories are required.');

		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');

		$this->setModal(true);
		$this->setAjax(true);
	}


}