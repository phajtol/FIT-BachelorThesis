<?php

use Nette\Diagnostics\Debugger,
    App\Helpers;

class PublicationFormRules {

    const CATEGORIES = 'PublicationFormRules::validateCategories';
	const AT_LEAST_ONE_CHECKED = 'PublicationFormRules::validateCheckboxList_AtLeastOneChecked';

    const JOURNAL_REQUIRED = 'PublicationFormRules::validateJournal_IsRequired';

    const AUTHOR_REQUIRED = 'PublicationFormRules::validateAuthor_IsRequired';
    const AUTHOR_OPTIONAL = 'PublicationFormRules::validateAuthor_IsOptional';
    const AUTHOR_SET_DEFAULT_VALUES = 'PublicationFormRules::validateAuthor_SetDefaultValues';

    const EDITOR_OPTIONAL = 'PublicationFormRules::validateEditor_IsOptional';
    const CHAPTER_OPTIONAL = 'PublicationFormRules::validateChapter_IsOptional';
    const PAGES_OPTIONAL = 'PublicationFormRules::validatePages_IsOptional';
    const BOOKTITLE_REQUIRED = 'PublicationFormRules::validateBooktitle_IsRequired';
    const SCHOOL_REQUIRED = 'PublicationFormRules::validateSchool_IsRequired';
    const INSTITUTION_REQUIRED = 'PublicationFormRules::validateInstitution_IsRequired';
    const PUBLISHER_REQUIRED = 'PublicationFormRules::validatePublisher_IsRequired';

    const CONFERENCE_REQUIRED = 'PublicationFormRules::validateConference_IsRequired';

    const CONFERENCE_YEAR_REQUIRED = 'PublicationFormRules::validateConferenceYear_IsRequired';

    const NOTE_REQUIRED = 'PublicationFormRules::validateNote_IsRequired';
    const CATEGORIES_SET_DEFAULT_VALUES = 'PublicationFormRules::validateCategories_SetDefaultValues';

    const GROUP_SET_DEFAULT_VALUES = 'PublicationFormRules::validateGroup_SetDefaultValues';




    const SPRINGER_FETCH_DATA = 'PublicationFormRules::springerFetchData';
    const BIBTEX_VALIDATE_STRUCTURE = 'PublicationFormRules::validateBibtex';
    const ISBN_VALID_FORM = 'PublicationFormRules::isbnValidForm';


    public static function validateJournal_IsRequired($item, $form) {

        $requiredTypes = array(
            'article',
        );

        if (in_array($form['pub_type']->value, $requiredTypes) && !$item->value) {
            return false;
        }

        return true;
    }

    public static function validateAuthor_IsOptional($item, $form) {

        $conditionalTypes = array(
            'book',
            'inbook',
        );

        if (in_array($form['pub_type']->value, $conditionalTypes) && !$item->value && !$form['editor']->value) {
            return false;
        }

        return true;
    }

    public static function validateAuthor_IsRequired($item, $form) {

        $requiredTypes = array(
            'article',
            'inproceedings',
            'incollection',
            'techreport',
            'mastersthesis',
            'phdthesis',
            'unpublished'
        );

        if (in_array($form['pub_type']->value, $requiredTypes) && !$item->value) {
            return false;
        }

        return true;
    }

    public static function validateAuthor_SetDefaultValues($item, $parent) {

        $authors = array();
        if (!empty($item->value) || $item->value == "0") {
            $authors = explode(" ", $item->value);
        }

        $selectedAuthors = array();

        foreach ($authors as $authorId) {
            $selectedAuthors[$authorId] = $parent->context->Author->getAuthorName($authorId);
        }

        $parent->template->selectedAuthors = $selectedAuthors;

        return true;
    }



    public static function validateEditor_IsOptional($item, $form) {

        $conditionalTypes = array(
            'book',
            'inbook',
        );

        if (in_array($form['pub_type']->value, $conditionalTypes) && !$item->value && !$form['authors']->value) {
            return false;
        }

        return true;
    }

    public static function validateChapter_IsOptional($item, $form) {

        $conditionalTypes = array(
            'inbook',
        );

        if (in_array($form['pub_type']->value, $conditionalTypes) && !$item->value && !$form['pages']->value) {
            return false;
        }

        return true;
    }

    public static function validatePages_IsOptional($item, $form) {

        $conditionalTypes = array(
            'inbook',
        );

        if (in_array($form['pub_type']->value, $conditionalTypes) && !$item->value && !$form['chapter']->value) {
            return false;
        }

        return true;
    }

    public static function validateBooktitle_IsRequired($item, $form) {

        $requiredTypes = array(
            'incollection',
        );

        if (in_array($form['pub_type']->value, $requiredTypes) && !$item->value) {
            return false;
        }

        return true;
    }

    public static function validateSchool_IsRequired($item, $form) {

        $requiredTypes = array(
            'mastersthesis',
            'phdthesis',
        );

        if (in_array($form['pub_type']->value, $requiredTypes) && !$item->value) {
            return false;
        }

        return true;
    }

    public static function validateInstitution_IsRequired($item, $form) {

        $requiredTypes = array(
            'techreport',
        );

        if (in_array($form['pub_type']->value, $requiredTypes) && !$item->value) {
            return false;
        }

        return true;
    }

    public static function validatePublisher_IsRequired($item, $form) {

        $requiredTypes = array(
            'book',
            'incollection',
            'inbook',
        );

        if (in_array($form['pub_type']->value, $requiredTypes) && !$item->value) {
            return false;
        }

        return true;
    }



    public static function validateConference_IsRequired($item, $form) {

        $requiredTypes = array(
            'inproceedings',
            'proceedings',
        );

        if (in_array($form['pub_type']->value, $requiredTypes) && !$item->value) {
            return false;
        }

        return true;
    }


    // mozna predelat - hodit do setdefaults
    public static function validateConferenceYear_IsRequired($item, $parent) {

        $formValues = $parent['publicationAddNewForm']->getHttpData();

        $requiredTypes = array(
            'inproceedings',
            'proceedings',
        );

        if (in_array($formValues['pub_type'], $requiredTypes) && !$formValues['conference_year_id']) {
            return false;
        } elseif (in_array($formValues['pub_type'], $requiredTypes)) {

            $conferenceYears = $parent->context->ConferenceYear->findAllBy(array('conference_id' => $formValues['conference']))->order("name ASC")->fetchPairs('id', 'name');

            $parent['publicationAddNewForm']['conference_year_id']->setItems($conferenceYears);
            $parent['publicationAddNewForm']['conference_year_id']->setDefaultValue($formValues['conference_year_id']);
        }

        return true;
    }


    public static function validateNote_IsRequired($item, $form) {

        $requiredTypes = array(
            'unpublished',
        );

        if (in_array($form['pub_type']->value, $requiredTypes) && !$item->value) {
            return false;
        }

        return true;
    }

    public static function validateCategories_SetDefaultValues($item, $parent) {

        $categories = array();
        if (!empty($item->value) || $item->value == "0") {
            $categories = explode(" ", $item->value);
        }

        $selectedCategories = array();

        foreach ($categories as $categoryId) {
            $selectedCategories[]['categories_id'] = $categoryId;
        }

        $parent->template->selectedCategories = $selectedCategories;

        return true;
    }


    public static function validateGroup_SetDefaultValues($item, $parent) {

        $group = array();
        if (!empty($item->value) || $item->value == "0") {
            $group = explode(" ", $item->value);
        }

        $selectedGroups = array();

        foreach ($group as $groupId) {
            $selectedGroups[]['group_id'] = $groupId;
        }

        $parent->template->selectedGroups = $selectedGroups;

        return true;
    }


    public static function springerFetchData($item, $parent) {

        if ($parent['publicationForm']['again']->value == 1) {
            return true;
        }

        $record = $parent->context->Author->getAuthorNameByAuthorName($parent['publicationAddNewAuthorForm']['name']->value, $parent['publicationAddNewAuthorForm']['middlename']->value, $item->value);

        if ($record) {
            $parent['publicationAddNewAuthorForm']->addError($record['name']);
            $parent['publicationAddNewAuthorForm']['again']->setValue(1); // set up new values
            return false;
        }

        return true;
    }

    public static function validateBibtex($item, $parent) {

        if ($parent['publicationImportForm']['type']->value == 'bibtex') {

            $parser = new Helpers\BibTexParser($item->value);
            if ($parser->is_error()) {
                $parent['publicationAddNewForm']->addError('Problems with structure of an imported definition');
                return false;
            }
        }

        return true;
    }

    public static function isbnValidForm($item, $parent) {

        if ($item->value) {
            $regex = '/\b(?:ISBN(?:: ?| ))?((?:97[89])?\d{9}[\dx])\b/i';

            if (preg_match($regex, str_replace('-', '', $item->value), $matches)) {
                return (10 === strlen($matches[1])) ? 1 : 2;
            }
            return false; // No valid ISBN found
        }

        return true;
    }



    /*
      $types = array(
      'misc',
      'book',
      'article',
      'inproceedings',
      'proceedings',
      'incollection',
      'inbook',
      'booklet',
      'manual',
      'techreport',
      'mastersthesis',
      'phdthesis',
      'unpublished'
      );
     */
	 
	public static function validateCheckboxList_AtLeastOneChecked($item) {
        if(!$item->value || empty($item->value) || !count($item->value)) {
            return false;
        }
        return true;
    }
	
	public static function validateCategories($item) {
        if(strlen($item->value)) {
            $arr = explode(" ", $item->value);
            foreach ($arr as $v) {
                if (!is_numeric($v)) return false;
            }
        }
        return true;
    }	 
}
