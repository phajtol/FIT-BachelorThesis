<?php

namespace App\CrudComponents\ConferenceYear;

use App\Model\Conference;
use App\Model\ConferenceYear;
use App\Model\ConferenceYearIsIndexed;

use PublicationFormRules;

class ConferenceYearForm extends \App\Forms\BaseForm implements \App\Forms\IMixtureForm {

	private $conferenceModel;
	private $conferenceYearModel;

	private $conferenceYearIsIndexedModel;

    /**
     * ConferenceYearForm constructor.
     * @param int $conferenceId
     * @param array $publishers - associative array [publisher_id => publisher_name, ..]
     * @param $documentIndexes
     * @param Conference $conferenceModel
     * @param ConferenceYear $conferenceYearModel
     * @param ConferenceYearIsIndexed $conferenceYearIsIndexedModel
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|null $name
     * @throws \Exception
     */
	public function __construct(int $conferenceId,
                                array $publishers,
                                $documentIndexes,
                                Conference $conferenceModel,
                                ConferenceYear $conferenceYearModel,
                                ConferenceYearIsIndexed $conferenceYearIsIndexedModel,
                                \Nette\ComponentModel\IContainer $parent = NULL,
                                string $name = NULL)
    {
		parent::__construct($parent, $name);
		$this->conferenceModel = $conferenceModel;
		$this->conferenceYearModel = $conferenceYearModel;
		$this->conferenceYearIsIndexedModel = $conferenceYearIsIndexedModel;

		$this->setModal(true);
		$this->setAjax(true);

		$this->addText('name', 'Name')
			->addRule($this::MAX_LENGTH, 'Name is way too long', 500)
			->setRequired('Name is required.');

		$this->addText('abbreviation', 'Abbreviation')
			->addRule($this::MAX_LENGTH, 'Abbreviation is way too long', 50)
			->setRequired('Abbreviation is required.');

		$this->addYear('w_year', 'Year');

        $this->addDate('deadline', 'Submission deadline')
            ->addRule(function ($deadlineEl) {
			        $deadlineDate = $deadlineEl->getValueTransformed();
			        $fromDate = $this['w_from']->getValueTransformed();
			        if($deadlineDate && $fromDate && $deadlineDate > $fromDate) {
				        return false;
			        }
			        return true;
		        }, 'Deadline must be before the start of the conference!')
            ->setRequired(false);

		$this->addDate('notification', 'Notification')
            ->addRule(function ($notificationEl) {
			        $notificationDate = $notificationEl->getValueTransformed();
			        $fromDate = $this['w_from']->getValueTransformed();
			        if ($notificationDate && $fromDate && $notificationDate > $fromDate) {
				        return false;
			        }
			        return true;
		        }, 'Notification date must be before the start of the conference!')
            ->setRequired(false);

		$this->addDate('finalversion', 'Final version')
            ->addRule(function ($finalversionEl) {
			        $finalversionDate = $finalversionEl->getValueTransformed();
			        $fromDate = $this['w_from']->getValueTransformed();
			        if ($finalversionDate && $fromDate && $finalversionDate > $fromDate) {
				        return false;
			        }
			        return true;
		        }, 'Final version date must be before the start of the conference!')
            ->setRequired(false);

        $this->addDate('w_from', 'From');
        $this->addDate('w_to', 'To');

        $this->addText('location', 'Location')
            ->addRule($this::MAX_LENGTH, 'Name is way too long', 500)
            ->setRequired(false);

        $this->addText('web', 'Web')
            ->setRequired(false)
            ->addRule($this::MAX_LENGTH, 'Web address is way too long', 500)
            ->addCondition($this::FILLED)
                ->addRule(self::URL, 'Web of conference must be a valid URL');

        $this->addHidden('isbn_count', '0');

		$cont = $this->addContainer("isbn");

		$this->addText('doi', 'DOI')
            ->addRule($this::MAX_LENGTH, 'DOI is way too long', 100)
            ->setRequired(false);

		$this->addTextArea('description', 'Description', 6, 8)
            ->addRule($this::MAX_LENGTH, 'Description is way too long', 1000)
            ->setRequired(false);

		$this->addSelect('publisher_id', 'Publisher', $publishers)->setPrompt(' ------- ');//->setRequired('Publisher is required.');

		$this->addMultiSelect('document_indexes', 'Indexed at', $documentIndexes);

		$this->addSubmit('send', 'Done');
		$this->addCloseButton('cancel', 'Cancel');
		$this->addHidden('id');

		$this->addIsbn();

		$this->addHidden('conference_id');

		if (!empty($conferenceId)) {
			$this->setConferenceId($conferenceId);
		}
	}

    /**
     * @param array $publishers - associative array: [publisher_id => publisher_name, ..]
     */
	public function setPublishers(array $publishers): void
    {
		$listEl = $this['publisher_id'];
		/** @var $listEl \Nette\Forms\Controls\SelectBox */
		$listEl->setItems($publishers);
	}

    /**
     *
     */
	public function removeConferencePart(): void
    {
		$fields = ['deadline', 'notification', 'finalversion', 'document_indexes'];
		foreach ($fields as $v) {
		    unset($this[$v]);
        }
	}

    /**
     *
     */
	public function removePublicationPart(): void
    {
		$fields = ['issn', 'doi', 'publisher_id', 'isbn'];
		foreach ($fields as $v) {
		    unset($this[$v]);
        }
	}

    /**
     * @param int $count
     */
	public function setIsbnCount(int $count): void
    {
		$this['isbn_count']->setValue($count);
	}

    /**
     * @return int
     */
	public function getIsbnCount(): int
    {
		return $this['isbn_count']->getValue();
	}

    /**
     * @param int $conferenceId
     * @throws \Exception
     */
    public function setConferenceId(int $conferenceId): void
    {
	    $conference = $this->conferenceModel->find($conferenceId);

	    if ($conference) {
		    $fieldsToCopyFromConference = ['name', 'abbreviation'];
			$fieldsToCopyFromLastConferenceYear = ['location', 'web', 'description', 'publisher_id'];
			$datesToCopyFromLastConferenceYear = ['deadline', 'notification', 'finalversion', 'w_from', 'w_to'];

			foreach ($fieldsToCopyFromConference as $f2c) {
			    if (isset($this[$f2c])) {
			        $this[$f2c]->setDefaultValue($conference->$f2c);
                }
			}

			$lastConferenceYears = $this->conferenceYearModel->findAllByConferenceId($conference->id)->order('w_year DESC')->limit(1);

			foreach ($lastConferenceYears as $lastConferenceYear) {
			    foreach($fieldsToCopyFromLastConferenceYear as $f2c) {
				    if (isset($this[$f2c])) {
                        $this[$f2c]->setDefaultValue($lastConferenceYear->$f2c);
                    }
			    }

				$shiftDateInterval = new \DateInterval('P1Y');
				foreach ($datesToCopyFromLastConferenceYear as $d2c) {
				    if (isset($this[$d2c]) && $lastConferenceYear->$d2c) {
                        $this[$d2c]->setDefaultValue($lastConferenceYear->$d2c->add($shiftDateInterval));
                    }
				}

				if (isset($this['document_indexes'])) {
				    $res = $this->conferenceYearIsIndexedModel->findAllByConferenceYearId($lastConferenceYear->id);
					$isIndexedAt = [];

					foreach ($res as $rec) {
					    $isIndexedAt[] = $rec->document_index_id;
                    }
                    $this['document_indexes']->setDefaultValue($isIndexedAt);
				}

				if (isset($this['w_year']) && $lastConferenceYear->w_year) {
                    $this['w_year']->setDefaultValue($lastConferenceYear->w_year + 1);
                }

				break; // maybe, in the future, iterate all conference years and fill missing fields?
			}

		    $this['conference_id']->setValue($conference->id);
	    }
	}

    /**
     *
     */
	public function addIsbn(): void
    {
		$count = $this['isbn_count']->getValue();
		$cont = $this['isbn'];

		for ($i = 0; $i < $count; $i++) {
			if (!empty($cont[$i])) {
				continue;
			}

			$cont2 = $cont->addContainer($i);
			$cont2->addText("isbn", "ISBN/ISSN")->setRequired(false);
			$cont2->addSelect("type", "Typ", ["ISBN" => "ISBN", "ISSN" => "ISSN"]);

			if (empty($cont2['type']->getValue())) {
				$cont2['type']->setValue("ISBN");
			}

			$cont2->addText("note", "Note")->setRequired(false);
		}
	}

}
