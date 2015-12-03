<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 18.3.2015
 * Time: 20:10
 */

namespace App\CrudComponents\Author;


class AuthorAddForm extends AuthorForm {


	public function __construct(\App\Model\Author $authorModel, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this['surname']->addRule(function($name, $form) use ($authorModel) {
			if($authorModel->findOneByName($form['name']->value, $form['middlename']->value, $form['surname']->value)){
				return false;
			} else return true;
		}, "Author witch such name already exists.", $this);

		$this->addConfirmableRule('surname', function() use ($authorModel) {

			$record = $authorModel->getAuthorNameByAuthorName(
				$this['name']->value,
				$this['middlename']->value,
				$this['surname']->value
			);

			if ($record) {
				return false;
			}

			return true;

		}, 'WARNING: Similar author has been found.');

	}

}