<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 18.3.2015
 * Time: 1:57
 */

namespace App\CrudComponents\ConferenceYear;


use App\Model\Conference;
use App\Model\ConferenceYear;
use App\Model\ConferenceYearIsIndexed;
use PublicationFormRules;

class ConferenceYearAddForm extends ConferenceYearForm {

	/**
	 * @param int $conference_id conference id
	 * @param array $publishers assoc [publisher_id => publisher_name, ..]
	 * @param \Nette\ComponentModel\IContainer $parent
	 * @param String $name
	 */
	public function __construct($conference_id, $publishers, $documentIndexes, Conference $conferenceModel, ConferenceYear $conferenceYearModel , ConferenceYearIsIndexed $conferenceYearIsIndexedModel, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($publishers, $documentIndexes, $parent, $name);

		$this['name']->addRule(function($name) use ($conferenceYearModel, $conferenceYearIsIndexedModel) {
			//if($conferenceYearModel->findOneByName($name->value)) return false; else return true;
			return true;
		}, "Name already exists.", $parent);

		$conference = $conferenceModel->find($conference_id);
		if($conference) {
			$fieldsToCopyFromConference = array('name', 'abbreviation');
			$fieldsToCopyFromLastConferenceYear = array('location', 'web', 'description', 'publisher_id');
			$datesToCopyFromLastConferenceYear = array('deadline', 'notification', 'finalversion', 'w_from', 'w_to');

			foreach($fieldsToCopyFromConference as $f2c) {
				if(isset($this[$f2c])) $this[$f2c]->setDefaultValue($conference->$f2c);
			}

			$lastConferenceYears = $conferenceYearModel->findAllByConferenceId($conference->id)->order('w_year DESC')->limit(1);
			foreach($lastConferenceYears as $lastConferenceYear) {

				foreach($fieldsToCopyFromLastConferenceYear as $f2c) {
					if(isset($this[$f2c]))
						$this[$f2c]->setDefaultValue($lastConferenceYear->$f2c);
				}

				$shiftDateInterval = new \DateInterval('P1Y');
				foreach($datesToCopyFromLastConferenceYear as $d2c) {
					if(isset($this[$d2c]) && $lastConferenceYear->$d2c)
						$this[$d2c]->setDefaultValue($lastConferenceYear->$d2c->add($shiftDateInterval));
				}

				if(isset($this['document_indexes'])) {
					$res = $conferenceYearIsIndexedModel->findAllByConferenceYearId($lastConferenceYear->id);
					$isIndexedAt = [];
					foreach ($res as $rec) $isIndexedAt[] = $rec->document_index_id;
					$this['document_indexes']->setDefaultValue($isIndexedAt);
				}

				if(isset($this['w_year']) && $lastConferenceYear->w_year)
					$this['w_year']->setDefaultValue($lastConferenceYear->w_year + 1);

				break; // maybe, in the future, iterate all conference years and fill missing fields?
			}


		}

		$this->addHidden('conference_id')->setValue($conference_id);
	}


}