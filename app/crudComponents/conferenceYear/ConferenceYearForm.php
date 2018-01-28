<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 18.3.2015
 * Time: 1:49
 */

namespace App\CrudComponents\ConferenceYear;

use App\Model\Conference;
use App\Model\ConferenceYear;
use App\Model\ConferenceYearIsIndexed;

use PublicationFormRules;

class ConferenceYearForm extends \App\Forms\BaseForm implements \App\Forms\IMixtureForm {

	/**
	 * @param array $publishers assoc [publisher_id => publisher_name, ..]
	 * @param \Nette\ComponentModel\IContainer $parent
	 * @param String $name
	 */
	public function __construct($conference_id, $publishers, $documentIndexes, Conference $conferenceModel, ConferenceYear $conferenceYearModel , ConferenceYearIsIndexed $conferenceYearIsIndexedModel, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->setModal(true);
		$this->setAjax(true);

		$this->addText('name', 'Name')
			->addRule($this::MAX_LENGTH, 'Name is way too long', 500)
			->setRequired('Name is required.');

		$this->addText('abbreviation', 'Abbreviation')
			->addRule($this::MAX_LENGTH, 'Abbreviation is way too long', 50)
			->setRequired('Abbreviation is required.');

		$this->addYear('w_year', 'Year');

		$this->addDate('w_from', 'From');
		$this->addDate('w_to', 'To');

		$this->addDate('deadline', 'Deadline')->addRule(function($deadlineEl) {
			$deadlineDate = $deadlineEl->getValueTransformed();
			$fromDate = $this['w_from']->getValueTransformed();
			if($deadlineDate && $fromDate && $deadlineDate > $fromDate) {
				return false;
			} else return true;
		}, 'Deadline must be before the start of the conference!');

		$this->addDate('notification', 'Notification')->addRule(function($notificationEl) {
			$notificationDate = $notificationEl->getValueTransformed();
			$fromDate = $this['w_from']->getValueTransformed();
			if($notificationDate && $fromDate && $notificationDate > $fromDate) {
				return false;
			} else return true;
		}, 'Notification date must be before the start of the conference!');

		$this->addDate('finalversion', 'Final version')->addRule(function($finalversionEl) {
			$finalversionDate = $finalversionEl->getValueTransformed();
			$fromDate = $this['w_from']->getValueTransformed();
			if($finalversionDate && $fromDate && $finalversionDate > $fromDate) {
				return false;
			} else return true;
		}, 'Final version date must be before the start of the conference!');

		$this->addText('location', 'Location')->addRule($this::MAX_LENGTH, 'Name is way too long', 500);
		$this->addText('web', 'Web')->addRule($this::MAX_LENGTH, 'Web address is way too long', 500)
			->addCondition(self::FILLED)->addRule(self::URL, 'Web of conference must be a valid URL');

		$this->addText('isbn', 'ISBN')->addCondition($this::FILLED)->addRule(PublicationFormRules::ISBN_VALID_FORM, "ISBN is not in correct form.", $parent);
		$this->addText('issn', 'ISSN')->addCondition($this::FILLED)->addRule($this::PATTERN, 'ISSN is not in correct form.', '[0-9]{4}-([0-9]{4}|[0-9]{3}X)');
		$this->addText('doi', 'DOI')->addRule($this::MAX_LENGTH, 'DOI is way too long', 100);

		$this->addTextArea('description', 'Description', 6, 8)->addRule($this::MAX_LENGTH, 'Description is way too long', 1000);

		$this->addSelect('publisher_id', 'Publisher', $publishers)->setPrompt(' ------- ');//->setRequired('Publisher is required.');

		$this->addMultiSelect('document_indexes', 'Indexed at', $documentIndexes);

		$this->addSubmit('send', 'Done');
		$this->addCloseButton('cancel', 'Cancel');
		$this->addHidden('id');

		if (!empty($conference_id)) {
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

	/**
	 * @param $publishers array assoc [publisher_id => publisher_name, ..]
	 */
	public function setPublishers($publishers){
		$listEl = $this["publisher_id"];
		/** @var $listEl \Nette\Forms\Controls\SelectBox */
		$listEl->setItems($publishers);
	}

	public function removeConferencePart() {
		$fields = array('deadline', 'notification', 'finalversion', 'document_indexes');
		foreach($fields as $v) unset($this[$v]);
	}

	public function removePublicationPart() {
		$fields = array('issn', 'doi', 'publisher_id', 'isbn');
		foreach($fields as $v) unset($this[$v]);
	}


}