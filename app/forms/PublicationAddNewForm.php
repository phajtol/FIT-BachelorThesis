<?php

namespace App\Forms;

use App\Model;


class PublicationAddNewFormFactory {

    /** @var Model\AttribStorage */
    protected $attribStorageModel;

    /** @var Model\Publisher */
    protected $publisherModel;

    /** @var Model\Journal */
    protected $journalModel;

    /** @var Model\Conference */
    protected $conferenceModel;

    /** @var Model\ConferenceYear */
    protected $conferenceYearModel;

    /** @var Model\Attribute */
    protected $attributeModel;

    /** @var Model\Publication */
    protected $publicationModel;

    /** @var Model\PublicationIsbn */
    protected $publicationIsbnModel;


    /**
     * PublicationAddNewFormFactory constructor.
     * @param Model\AttribStorage $attribStorageModel
     * @param Model\Publisher $publisherModel
     * @param Model\Journal $journalModel
     * @param Model\Conference $conferenceModel
     * @param Model\ConferenceYear $conferenceYearModel
     * @param Model\Attribute $attributeModel
     * @param Model\Publication $publicationModel
     * @param Model\PublicationIsbn $publicationIsbnModel
     */
    public function __construct(Model\AttribStorage $attribStorageModel,
                                Model\Publisher $publisherModel,
                                Model\Journal $journalModel,
                                Model\Conference $conferenceModel,
                                Model\ConferenceYear $conferenceYearModel,
                                Model\Attribute $attributeModel,
                                Model\Publication $publicationModel,
                                Model\PublicationIsbn $publicationIsbnModel)
    {
        $this->attribStorageModel = $attribStorageModel;
        $this->publisherModel = $publisherModel;
        $this->journalModel = $journalModel;
        $this->conferenceModel = $conferenceModel;
        $this->conferenceYearModel = $conferenceYearModel;
        $this->attributeModel = $attributeModel;
        $this->publicationModel = $publicationModel;
        $this->publicationIsbnModel = $publicationIsbnModel;
      }

    /**
     * @param null $publication_id
     * @param $selectedConferenceId
     * @param $typesOfPublication
     * @param $parent
     * @param $onSuccess
     * @return BaseForm
     */
      public function create($publication_id = null, $selectedConferenceId, $typesOfPublication, $parent, $onSuccess): BaseForm
      {

        $form = new BaseForm();

        $form->addText('title', 'Title')
            ->addRule($form::MAX_LENGTH, 'Title is way too long', 500)
            ->setRequired('Title is required.');

        if (!$publication_id) {
          $that = $this;
            $form['title']->addRule(function ($item) use ($that) {
                if ($that->publicationModel->findOneBy(['title' => $item->value])) {
                    return false;
                }
                return true;
            }, "Title already exists.", $parent);
        }

        $form->addTextArea('abstract', 'Abstract', 6, 8)
            ->addRule($form::MAX_LENGTH, 'Abstract is way too long', 20000)
            ->setRequired(false);

        $form->addSelect('pub_type', 'Type of publication', $typesOfPublication)
            ->setPrompt(' ------- ')
            ->setRequired('Type of publication is required.');

        $form->addText('categories')
            ->addRule(\PublicationFormRules::CATEGORIES_SET_DEFAULT_VALUES, "-", $parent)
            ->setRequired(false);

        $form->addHidden('group')
            ->addRule(\PublicationFormRules::GROUP_SET_DEFAULT_VALUES, "-", $parent)
            ->setRequired(false);

        $form->addText('authors')
            ->addRule(\PublicationFormRules::AUTHOR_REQUIRED, "Author(s) is/are required.", $form)
            ->addRule(\PublicationFormRules::AUTHOR_OPTIONAL, "Select Author(s) or fill Editor.", $form)
            ->addRule(\PublicationFormRules::AUTHOR_SET_DEFAULT_VALUES, "-", $parent)
            ->setRequired(false);

        $form->addText('volume', 'Volume')
            ->addRule($form::MAX_LENGTH, 'Volume is way too long', 50)
            ->setRequired(false);

        $form->addText('number', 'Number')
            ->addRule($form::MAX_LENGTH, 'Number is way too long', 50)
            ->setRequired(false);

        $form->addText('chapter', 'Chapter')
            ->addRule($form::MAX_LENGTH, 'Chapter is way too long', 200)
            ->addRule(\PublicationFormRules::CHAPTER_OPTIONAL, "Fill Chapter or Pages.", $form)
            ->setRequired(false);

        $form->addText('pages', 'Pages')
            ->setRequired(false)
            ->addRule(\PublicationFormRules::PAGES_OPTIONAL, "Fill Pages or Chapter.", $form)
            ->addCondition($form::FILLED)
            ->addRule($form::PATTERN, 'Pages must be in P-P form.', '([0-9]{1,5})|([0-9]{1,5}-[0-9]{1,5})');

        $form->addText('editor', 'Editor')
            ->addRule($form::MAX_LENGTH, 'Editor is way too long', 200)
            ->addRule(\PublicationFormRules::EDITOR_OPTIONAL, "Fill Editor or select Author(s).", $form)
            ->setRequired(false);

        $form->addText('edition', 'Edition')
            ->addRule($form::MAX_LENGTH, 'Edition is way too long', 200)
            ->setRequired(false);

        $form->addText('address', 'Address')
            ->addRule($form::MAX_LENGTH, 'Address is way too long', 500)
            ->setRequired(false);

        $form->addText('booktitle', 'Booktitle')
            ->addRule($form::MAX_LENGTH, 'Booktitle is way too long', 500)
            ->addRule(\PublicationFormRules::BOOKTITLE_REQUIRED, "Booktitle is required.", $form)
            ->setRequired(false);

        $form->addText('school', 'School')
            ->addRule($form::MAX_LENGTH, 'School is way too long', 200)
            ->addRule(\PublicationFormRules::SCHOOL_REQUIRED, "School is required.", $form)
            ->setRequired(false);

        $form->addText('institution', 'Institution')
            ->addRule($form::MAX_LENGTH, 'Institution is way too long', 200)
            ->addRule(\PublicationFormRules::INSTITUTION_REQUIRED, "Institution is required.", $form)
            ->setRequired(false);

        $form->addText('type_of_report', 'Type of report')
            ->setRequired(false);

        $form->addSelect('publisher_id', 'Publisher',$this->publisherModel->findAll()->order("name ASC")->fetchPairs('id', 'name'))
            ->setPrompt(' ------- ')
            ->addRule(\PublicationFormRules::PUBLISHER_REQUIRED, "Publisher is required.", $form)
            ->setRequired(false);

        $form->addSelect('journal_id', 'Journal', $this->journalModel->findAll()->order("name ASC")->fetchPairs('id', 'name'))
            ->setPrompt(' ------- ')
            ->addRule(\PublicationFormRules::JOURNAL_REQUIRED, "Journal is required.", $form)
            ->setRequired(false);

        $form->addSelect('conference', 'Conference', $this->conferences = $this->conferenceModel->getConferenceForSelectbox())
            ->setPrompt(' ------- ')
            ->addRule(\PublicationFormRules::CONFERENCE_REQUIRED, "Conference is required.", $form)
            ->setRequired(false);

        $form->addSelect('conference_year_id', 'Year of Conference', $this->conferenceYearModel->getConferenceYearForSelectbox($selectedConferenceId))
            ->setPrompt(' ------- ')
            ->addRule(\PublicationFormRules::CONFERENCE_YEAR_REQUIRED, "Year of Conference is required.", $parent)
            ->setRequired(false);


        if ($publication_id) {
            $count = $this->publicationIsbnModel->findAllBy(["publication_id" => $publication_id])->count('*');
        } else {
            $count = 1;
        }

        $form->addHidden('isbn_count')
          ->setDefaultValue($count);
        $cont = $form->addContainer("isbn");

        $form->addText('doi', 'DOI')
            ->addRule($form::MAX_LENGTH, 'DOI is way too long', 100)
            ->setRequired(false);

        $form->addText('howpublished', 'Howpublished')
            ->addRule($form::MAX_LENGTH, 'Howpublished is way too long', 200)
            ->setRequired(false);

        $form->addSelect('issue_year', 'Year of publication', array_combine(range(date("Y"), 1900), range(date("Y"),1900)))
            ->setPrompt(' ------- ')
            ->setRequired(false);

        $form->addSelect('issue_month', 'Month of publication', array_combine(range(1, 12), range(1,12)))
            ->setPrompt(' ------- ')
            ->setRequired(false);

        $form['issue_year']->addConditionOn($form['issue_month'], $form::FILLED)
            ->addRule($form::FILLED,"Year of publication year is required if Month of publication is not empty.")
            ->setRequired(false);

        $form->addText('organization', 'Organization')
            ->addRule($form::MAX_LENGTH, 'Organization is way too long', 200)
            ->setRequired(false);

        $form->addText('url', 'URL')
            ->setRequired(false)
            ->addRule($form::MAX_LENGTH, 'URL is way too long', 500)
            ->addCondition($form::FILLED)
            ->addRule($form::URL, 'URL is not in correct form.');

        $attributes = $this->attributeModel->findAll()->order("name ASC");
        $cont = $form->addContainer("attributes");

        foreach ($attributes as $atrib) {
            $cont->addText($atrib->id, $atrib->name . ' (' . $atrib->description . ')')
                ->setRequired(false);
        }

        $form->addTextArea('note', 'Note', 6, 8)
            ->addRule(\PublicationFormRules::NOTE_REQUIRED, "Note is required.", $form)
            ->setRequired(false);

        $form->addMultiUpload("upload", "Attachments")
            ->setRequired(false);

        $form->addHidden('id');

        $form->addSubmit('cancel', 'Cancel')
            ->setValidationScope(NULL)->onClick[] = function () use ($parent) {
                $parent->redirect('Publication:showall');
            };

        $form->addSubmit('send', 'Done');

        $form->onSuccess[] = function ($form) use ($onSuccess) {
            $onSuccess($form);
        };

        return $form;
    }

}
