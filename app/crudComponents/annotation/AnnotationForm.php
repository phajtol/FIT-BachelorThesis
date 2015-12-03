<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.5.2015
 * Time: 11:54
 */

namespace App\CrudComponents\Annotation;


class AnnotationForm extends \App\Forms\BaseForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addTextArea('text', 'Text', 6, 8)->addRule($this::MAX_LENGTH, 'Text is way too long', 20000)->setRequired('Text is required.');
		$this->addRadioList('global_scope', 'Visibility', array('0' => 'Private', '1' => 'Global'))->setDefaultValue('0')->setRequired('Visibility is required.');

		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');

		$this->action .= '#annotations';

		$this->setModal(true);
		$this->setAjax(true);
	}

}