<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 18.3.2015
 * Time: 1:59
 */

namespace App\CrudComponents\ConferenceYear;


class ConferenceYearEditForm extends ConferenceYearForm {

	/**
	 * @param array $publishers assoc [publisher_id => publisher_name, ..]
	 * @param \Nette\ComponentModel\IContainer $parent
	 * @param String $name
	 */
	public function __construct($publishers, $documentIndexes, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($publishers, $documentIndexes, $parent, $name);

		$this->addHidden('id')->setRequired(true);
	}

}