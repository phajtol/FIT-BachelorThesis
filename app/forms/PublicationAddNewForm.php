<?php

use App\Forms\BaseForm;
use App\Model\Publication;
use Nette\Application\UI,
    Nette\ComponentModel\IContainer,
    Nette\Diagnostics\Debugger;
use Nette\Forms\Controls\TextInput;

class PublicationAddNewForm extends BaseForm {


    public function __construct($publicationId = null, $typesOfPublication, $attribStorage, $publishers, $journals, $conferences, $conferencesYears, $attributes,
                                Publication $publicationModel,
                                IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        Debugger::fireLog('PublicationAddNewForm()');

        $this->addText('title', 'Title')->addRule($this::MAX_LENGTH, 'Title is way too long', 500)->setRequired('Title is required.');
        if (!$publicationId) {
            $this['title']->addRule(function($item) use ($publicationModel) {
                if($publicationModel->findOneBy(array('title' => $item->value))) return false;
                return true;
            }, "Title already exists.", $parent);
        }
        $this->addTextArea('abstract', 'Abstract', 6, 8)->addRule($this::MAX_LENGTH, 'Abstract is way too long', 20000);
        $this->addSelect('pub_type', 'Type of publication', $typesOfPublication)->setPrompt(' ------- ')->setRequired('Type of publication is required.');
        $this->addText('categories')->addRule(PublicationFormRules::CATEGORIES_SET_DEFAULT_VALUES, "-", $parent);
        $this->addHidden('group')->addRule(PublicationFormRules::GROUP_SET_DEFAULT_VALUES, "-", $parent);
        $this->addText('authors')
                ->addRule(PublicationFormRules::AUTHOR_REQUIRED, "Author(s) is/are required.", $this)
                ->addRule(PublicationFormRules::AUTHOR_OPTIONAL, "Select Author(s) or fill Editor.", $this)
                ->addRule(PublicationFormRules::AUTHOR_SET_DEFAULT_VALUES, "-", $parent);
        $this->addText('volume', 'Volume')->addRule($this::MAX_LENGTH, 'Volume is way too long', 50);
        $this->addText('number', 'Number')->addRule($this::MAX_LENGTH, 'Number is way too long', 50);
        $this->addText('chapter', 'Chapter')->addRule($this::MAX_LENGTH, 'Chapter is way too long', 200)->addRule(PublicationFormRules::CHAPTER_OPTIONAL, "Fill Chapter or Pages.", $this);
        $this->addText('pages', 'Pages')->addRule(PublicationFormRules::PAGES_OPTIONAL, "Fill Pages or Chapter.", $this)->addCondition($this::FILLED)->addRule($this::PATTERN, 'Pages must be in P-P form.', '([0-9]{1,5})|([0-9]{1,5}-[0-9]{1,5})');
        $this->addText('editor', 'Editor')->addRule($this::MAX_LENGTH, 'Editor is way too long', 200)->addRule(PublicationFormRules::EDITOR_OPTIONAL, "Fill Editor or select Author(s).", $this);
        $this->addText('edition', 'Edition')->addRule($this::MAX_LENGTH, 'Edition is way too long', 200);
        $this->addText('address', 'Address')->addRule($this::MAX_LENGTH, 'Address is way too long', 500);
        $this->addText('booktitle', 'Booktitle')->addRule($this::MAX_LENGTH, 'Booktitle is way too long', 500)->addRule(PublicationFormRules::BOOKTITLE_REQUIRED, "Booktitle is required.", $this);
        $this->addText('school', 'School')->addRule($this::MAX_LENGTH, 'School is way too long', 200)->addRule(PublicationFormRules::SCHOOL_REQUIRED, "School is required.", $this);
        $this->addText('institution', 'Institution')->addRule($this::MAX_LENGTH, 'Institution is way too long', 200)->addRule(PublicationFormRules::INSTITUTION_REQUIRED, "Institution is required.", $this);
        $this->addText('type_of_report', 'Type of report');
        $this->addSelect('publisher_id', 'Publisher', $publishers)->setPrompt(' ------- ')->addRule(PublicationFormRules::PUBLISHER_REQUIRED, "Publisher is required.", $this);
        $this->addSelect('journal_id', 'Journal', $journals)->setPrompt(' ------- ')->addRule(PublicationFormRules::JOURNAL_REQUIRED, "Journal is required.", $this);
        $this->addSelect('conference', 'Conference', $conferences)->setPrompt(' ------- ')->addRule(PublicationFormRules::CONFERENCE_REQUIRED, "Conference is required.", $this);
        $this->addSelect('conference_year_id', 'Year of Conference', $conferencesYears)->setPrompt(' ------- ')->addRule(PublicationFormRules::CONFERENCE_YEAR_REQUIRED, "Year of Conference is required.", $parent);
        $this->addText('isbn', 'ISBN')->addCondition($this::FILLED)->addRule(PublicationFormRules::ISBN_VALID_FORM, "ISBN is not in correct form.", $parent);
        $this->addText('doi', 'DOI')->addRule($this::MAX_LENGTH, 'DOI is way too long', 100);
        $this->addText('howpublished', 'Howpublished')->addRule($this::MAX_LENGTH, 'Howpublished is way too long', 200);
        $this->addSelect('issue_year', 'Year of publication', array_combine(range(date("Y"), 1900), range(date("Y"),1900)))->setPrompt(' ------- ');
        $this->addSelect('issue_month', 'Month of publication', array_combine(range(1, 12), range(1,12)))->setPrompt(' ------- ');
        $this['issue_year']->addConditionOn($this['issue_month'], $this::FILLED)->addRule($this::FILLED,"Year of publication year is required if Month of publication is not empty.");
        $this->addText('organization', 'Organization')->addRule($this::MAX_LENGTH, 'Organization is way too long', 200);
        $this->addText('url', 'URL')->addRule($this::MAX_LENGTH, 'URL is way too long', 500)->addCondition($this::FILLED)->addRule($this::URL, 'URL is not in correct form.');

        foreach ($attributes as $atrib) {
            $this->addText('attributes_' . $atrib->id, $atrib->name . ' (' . $atrib->description . ')');
        }

        if ($publicationId) {
            foreach ($attribStorage as $atSt) {
                $this['attributes_' . $atSt->attributes_id]->setDefaultValue($atSt->value);
            }
        }

        $this->addTextArea('note', 'Note', 6, 8)->addRule(PublicationFormRules::NOTE_REQUIRED, "Note is required.", $this);
        $this->addMultipleFileUpload("upload", "Attachments");
        $this->addHidden('id');

        $this->addSubmit('cancel', 'Cancel')->setValidationScope(NULL)->onClick[] = function () use ($parent) {
            $parent->redirect('Publication:showall');
        };
        $this->addSubmit('send', 'Done');


    }

    public function setAttributes($attributes) {
        $attributeArr = [];
        foreach($attributes as $attribute) $attributeArr[] = $attribute;

        foreach($this->getControls() as $control){
            if(substr($control->name, 0, strlen('attributes_')) == 'attributes_'
                && !count(array_filter($attributeArr, function(&$a) use ($control) { return $control->name == 'attributes_' . $a->id ; }))
            ){
                unset($this[$control->name]);
            }
        }
        foreach($attributes as $attrib) {
            $label = $attrib->name . ' (' . $attrib->description . ')';
            $key = 'attributes_' . $attrib->id;
            if(!isset($this[$key])) {
                $this->addText($key, $label);
            } else {
                $this[$key]->caption = $label;
            }
        }
    }

    public function updateConferences(){

    }

}
