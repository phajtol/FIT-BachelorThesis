<?php

namespace App\Presenters;

use Nette,
    App\Model,
    Nette\Diagnostics\Debugger,
    App\Helpers;

class PublicationPresenter extends SecuredPresenter {

    public $types;
    public $authors;
    public $publishers;
    public $conferencesYears;
    public $allPubs;
    public $currentAuthorsString;
    public $currentCategoriesString;

    public $files;
    public $group;
    public $attributes;
    public $attribStorage;



    public $publication;
    public $conferences;
    public $conferenceYears;
    public $journals;
    public $pdfParser;
    public $fulltext;
    public $functions;

    //--

    /** @var  Model\Publication */
    protected $publicationModel;

    /** @var  Model\Publisher */
    protected $publisherModel;

    /** @var  Model\Journal */
    protected $journalModel;

    /** @var  Model\Conference */
    protected $conferenceModel;

    /** @var  Model\ConferenceYear */
    protected $conferenceYearModel;

    /** @var  Model\Attribute */
    protected $attributeModel;

    /** @var  Model\Author */
    protected $authorModel;

    /** @var  Model\Group */
    protected $groupModel;

    /** @var  Model\Files */
    protected $filesModel;

    /**
     * @var Model\Annotation
     * @autowire
     */
    protected $annotationModel;

    /**
     * @var Model\Reference
     * @autowire
     */
    protected $referenceModel;
    
    /**
     * @var Model\AttribStorage
     * @autowire
     */
    protected $attribStorageModel;

    /**
     * @var Model\Springer
     * @autowire
     */
    protected $springerService;

    /**
     * @var Model\GroupHasPublication
     * @autowire
     */
    protected $groupHasPublicationModel;

    /**
     * @var Model\CategoriesHasPublication
     * @autowire
     */
    protected $categoriesHasPublicationModel;

    /**
     * @var Model\AuthorHasPublication
     * @autowire
     */
    protected $authorHasPublicationModel;

    /**
     * @var Model\Documents
     * @autowire
     */
    protected $documentsModel;

    /**
     * @var Model\SubmitterHasPublication
     * @autowire
     */
    protected $submitterHasPublicationModel;

    /**
     * @var  Model\Format
     * @autowire
     */
    protected $formatModel;

    //--

    /** @var  \App\Factories\IAnnotationCrudFactory */
    protected $annotationCrudFactory;

    /** @var  \App\Factories\IPublisherCrudFactory */
    protected $publisherCrudFactory;

    /** @var  \App\Factories\IJournalCrudFactory */
    protected $journalCrudFactory;

    /** @var  \App\Factories\IConferenceYearCrudFactory */
    protected $conferenceYearCrudFactory;

    /** @var  \App\Factories\IConferenceCrudFactory */
    protected $conferenceCrudFactory;

    /** @var  \App\Factories\IAttributeCrudFactory */
    protected $attributeCrudFactory;

    /** @var  \App\Factories\IGroupCrudFactory */
    protected $groupCrudFactory;

    /** @var  \App\Factories\IAuthorCrudFactory */
    protected $authorCrudFactory;

    /** @var  \App\Factories\IReferenceCrudFactory */
    protected $referenceCrudFactory;

    /** @var  \App\Factories\IPublicationCategoryListFactory */
    protected $publicationCategoryListFactory;

    // --

    protected $publicationId;

    protected $selectedPublisherId;
    protected $selectedJournalId;

    /** @persistent */
    public $selectedConferenceId;

    /** @persistent */
    public $selectedConferenceYearId;

    protected $selectedGroupId;
    protected $selectedAuthorId;


    /**
     * @param Model\Publisher $publisherModel
     */
    public function injectPublisherModel(Model\Publisher $publisherModel) {
        $this->publisherModel = $publisherModel;
    }

    /**
     * @param Model\Journal $journalModel
     */
    public function injectJournalModel(Model\Journal $journalModel) {
        $this->journalModel = $journalModel;
    }

    /**
     * @param Model\ConferenceYear $conferenceYearModel
     */
    public function injectConferenceYearModel(Model\ConferenceYear $conferenceYearModel) {
        $this->conferenceYearModel = $conferenceYearModel;
    }

    /**
     * @param Model\Conference $conferenceModel
     */
    public function injectConferenceModel(Model\Conference $conferenceModel) {
        $this->conferenceModel = $conferenceModel;
    }

    /**
     * @param Model\Attribute $attributeModel
     */
    public function injectAttributeModel(Model\Attribute $attributeModel) {
        $this->attributeModel = $attributeModel;
    }

    /**
     * @param Model\Publication $publicationModel
     */
    public function injectPublicationModel(Model\Publication $publicationModel) {
        $this->publicationModel = $publicationModel;
    }

    /**
     * @param Model\Files $filesModel
     */
    public function injectFilesModel(Model\Files $filesModel) {
        $this->filesModel = $filesModel;
    }

    /**
     * @param Model\Author $authorModel
     */
    public function injectAuthorModel(Model\Author $authorModel) {
        $this->authorModel = $authorModel;
    }

    /**
     * @param Model\Group $groupModel
     */
    public function injectGroupModel(Model\Group $groupModel) {
        $this->groupModel = $groupModel;
    }


    
    //  --

    /**
     * @param \App\Factories\IAnnotationCrudFactory $annotationCrudFactory
     */
    public function injectAnnotationCrudFactory(\App\Factories\IAnnotationCrudFactory $annotationCrudFactory) {
        $this->annotationCrudFactory = $annotationCrudFactory;
    }

    /**
     * @param \App\Factories\IPublisherCrudFactory $publisherCrudFactory
     */
    public function injectPublisherCrudFactory(\App\Factories\IPublisherCrudFactory $publisherCrudFactory) {
        $this->publisherCrudFactory = $publisherCrudFactory;
    }

    /**
     * @param \App\Factories\IJournalCrudFactory $journalCrudFactory
     */
    public function injectJournalCrudFactory(\App\Factories\IJournalCrudFactory $journalCrudFactory) {
        $this->journalCrudFactory = $journalCrudFactory;
    }

    /**
     * @param \App\Factories\IConferenceYearCrudFactory $conferenceYearCrudFactory
     */
    public function injectConferenceYearCrudFactory(\App\Factories\IConferenceYearCrudFactory $conferenceYearCrudFactory) {
        $this->conferenceYearCrudFactory = $conferenceYearCrudFactory;
    }

    /**
     * @param \App\Factories\IConferenceCrudFactory $conferenceCrudFactory
     */
    public function injectConferenceCrudFactory(\App\Factories\IConferenceCrudFactory $conferenceCrudFactory) {
        $this->conferenceCrudFactory = $conferenceCrudFactory;
    }

    /**
     * @param \App\Factories\IAttributeCrudFactory $attributeCrudFactory
     */
    public function injectAttributeCrudFactory(\App\Factories\IAttributeCrudFactory $attributeCrudFactory) {
        $this->attributeCrudFactory = $attributeCrudFactory;
    }

    /**
     * @param \App\Factories\IGroupCrudFactory $groupCrudFactory
     */
    public function injectGroupCrudFactory(\App\Factories\IGroupCrudFactory $groupCrudFactory) {
        $this->groupCrudFactory = $groupCrudFactory;
    }

    /**
     * @param \App\Factories\IAuthorCrudFactory $authorCrudFactory
     */
    public function injectAuthorCrudFactory(\App\Factories\IAuthorCrudFactory $authorCrudFactory) {
        $this->authorCrudFactory = $authorCrudFactory;
    }

    /**
     * @param \App\Factories\IReferenceCrudFactory $referenceCrudFactory
     */
    public function injectReferenceCrudFactory(\App\Factories\IReferenceCrudFactory $referenceCrudFactory) {
        $this->referenceCrudFactory = $referenceCrudFactory;
    }
    
    /**
     * @param \App\Factories\IPublicationCategoryListFactory $publicationCategoryListFactory
     */
    public function injectPublicationCategoryListFactory(\App\Factories\IPublicationCategoryListFactory $publicationCategoryListFactory) {
        $this->publicationCategoryListFactory = $publicationCategoryListFactory;
    }


    // --

    

    public function __construct(Nette\Database\Context $database) {
        $this->functions = new Helpers\Functions();
    }

    protected function startup() {
        parent::startup();
        $this->fulltext = '';

        $this->publication = array();
        $this->conferences = array();
        $this->conferencesYears = array();

        $this->drawAllowed = false;

        $this->types = array(
            'misc' => 'Misc (other kinds of publication)',
            'book' => 'Book (a published book)',
            'article' => 'Article (an article from a magazine or a journal)',
            'inproceedings' => 'InProceedings (an article in a conference proceedings)',
            'proceedings' => 'Proceedings (the proceedings of a conference)',
            'incollection' => 'InCollection (a section of a book having its own title)',
            'inbook' => 'InBook (a section of a book)',
            'booklet' => 'Booklet (a bound work without a named publisher or sponsor)',
            'manual' => 'Manual (technical manual)',
            'techreport' => 'Techreport (a technical report from an institution)',
            'mastersthesis' => 'Mastersthesis (master thesis)',
            'phdthesis' => 'Phdthesis (Ph.D. thesis)',
            'unpublished' => 'Unpublished (an unpublished article, book, thesis, etc.)'
        );
    }
    public function beforeRender() {
        parent::beforeRender();
        $recordsStarredTemp = $this->submitterHasPublicationModel->findAllBy(array('submitter_id' => $this->user->id));
        $this->template->recordsStarred = array();

        foreach ($recordsStarredTemp as $record) {
            $this->template->recordsStarred[] = $record->publication_id;
        }
    }
    public function actionDefault() {
        
    }

    public function renderDefault() {
        
    }

    protected function createComponentPublicationImportForm($name) {
        $form = new \PublicationImportForm($this, $name);
        $form->onSuccess[] = $this->publicationImportFormSucceeded;
        return $form;
    }

    protected function createComponentReferenceCrud($name) {
        $c = $this->referenceCrudFactory->create($this->publicationId);
        if(!$this->publicationId) $c->disallowAction('add');
        $cbFn = function(){
            $references = $this->referenceModel->findAllBy(array('publication_id' => $this->publication->id))->order("id ASC");

            $this->template->references = $references;

            $this->successFlashMessage('Operation has been completed successfully.');
            $this->redrawControl('publicationReferencesData');
        };
        $c->onAdd[] = $cbFn;
        $c->onDelete[] = $cbFn;
        $c->onEdit[] = $cbFn;
        return $c;
    }
    
    protected function createComponentAnnotationCrud(){
        $c = $this->annotationCrudFactory->create($this->publicationId);
        if(!$this->publicationId) $c->disallowAction('add');
        $cbFn = function(){
            if ($this->user->isInRole('admin')) {
                $annotations = $this->annotationModel->findAllBy(array('publication_id' => $this->publication->id))->order("id ASC");
            } else {
                $annotations = $this->annotationModel->findAllForReaderOrSubmitter($this->publication->id, $this->user->id);
            }

            $this->template->annotations = $annotations;

            $this->successFlashMessage('Operation has been completed successfully.');
            $this->redrawControl('publicationAnnotationData');
        };
        $c->onAdd[] = $cbFn;
        $c->onDelete[] = $cbFn;
        $c->onEdit[] = $cbFn;
        return $c;
    }

    public function publicationImportFormSucceeded($form) {

        Debugger::fireLog('publicationImportFormSucceeded');

        $formValues = $form->getValues();

        $definitionTemplate = preg_replace('/\r+/', '<br />', $formValues['definition']);
        $definitionTemplate = preg_replace('/\n+/', '<br />', $definitionTemplate);

        if ($formValues['type'] == "bibtex") {
            $definition = preg_replace('/\r+/', '', $formValues['definition']);
            $definition = preg_replace('/\n+/', '', $definition);

            $parser = new Helpers\BibTexParser($definition);
            $pub_type = $fields = $authors = null;
            $parser->parse($pub_type, $fields, $authors);
            $bibtex = Helpers\Bibtex::create($pub_type);
            $report = $bibtex->validate($fields);
        } elseif ($formValues['type'] == "endnote") {
            $parser = new Helpers\EndNoteRefWorksParser($formValues['definition'], 'endnote');
            $parser->readLines();
            $fields = $parser->getFields();
            $pub_type = isset($fields['pub_type']) ? $fields['pub_type'] : 'misc';
            $authors = isset($fields['authors']) ? $fields['authors'] : array();
        } elseif ($formValues['type'] == "refworks") {
            $parser = new Helpers\EndNoteRefWorksParser($formValues['definition'], 'refworks');
            $parser->readLines();
            $fields = $parser->getFields();
            $pub_type = isset($fields['pub_type']) ? $fields['pub_type'] : 'misc';
            $authors = isset($fields['authors']) ? $fields['authors'] : array();
        }


        $selectedAuthors = array();

        foreach ($authors as $author) {
            $tempAuthor = $this->authorModel->getAuthorNameByAuthorName($author['name'], $author['middlename'], $author['surname']);
            if ($tempAuthor) {
                $selectedAuthors[$tempAuthor['id']] = $tempAuthor['name'];
            }
        }

        $this->template->selectedAuthors = $selectedAuthors;

        if ($pub_type == 'inproceedings' || $pub_type == 'proceedings') {

            $isbn = "";
            $location = "";
            $conference = "";
            $conference_year_id = "";
            $conference_id = "";
            $isbn = isset($fields['isbn']) ? $fields['isbn'] : '';
            $location = isset($fields['location']) ? $fields['location'] : '';
            $conference = isset($fields['booktitle']) ? $fields['booktitle'] : '';
            if ($conference) {
                $conference_year_id = $this->conferenceYearModel->findOneBy(array('name' => $conference));
            }
            if ($isbn) {
                $conference_year_id2 = $this->conferenceYearModel->findOneBy(array('isbn' => $isbn));
                if ($conference_year_id2) {
                    $conference_year_id = $conference_year_id2;
                }
            }


            if ($conference_year_id) {

                $selectedConferenceYear = $this->conferenceYearModel->find($conference_year_id);

                $this->selectedConferenceYearId = $conference_year_id;
                $this->selectedConferenceId = $selectedConferenceYear->conference_id;

                $this->loadConferenceYears(true);

                $this->publication['conference'] = $selectedConferenceYear->conference_id;
                $this->publication['conference_year_id'] = $conference_year_id;
            } else {

                /** @var $conferenceForm \App\CrudComponents\Conference\ConferenceAddForm */
                $conferenceForm = $this['conferenceCrud']['conferenceAddForm'];

                $conferenceForm['name']->setDefaultValue(Helpers\Func::getValOrNull($fields, 'booktitle'));
            }
        }

        //If the journal is detected, the appropriate fields are filled
        if ($pub_type == 'article') {
            $issn = "";
            $journal = "";
            $journal_id = "";
            $issn = isset($fields['issn']) ? $fields['issn'] : '';
            $journal = isset($fields['journal']) ? $fields['journal'] : '';
            if ($journal) {
                $journal_id = $this->journalModel->findOneBy(array('name' => $journal));
            }
            if ($issn) {
                $journal_id2 = $this->journalModel->findOneBy(array('issn' => $issn));

                if ($journal_id2) {
                    $journal_id = $journal_id2;
                }
            }

            if ($journal_id) {
                $this->publication['journal_id'] = $journal_id->id;
            }
        }

        if ($pub_type != 'article') {
            $issn = "";
        }

        if ($pub_type != 'inproceedings' && $pub_type != 'proceedings') {
            //If the publisher is detected, the appropriate fields are filled
            $publisher = $issn = isset($fields['publisher']) ? $fields['publisher'] : '';
            $publisher_id = $this->publisherModel->findOneBy(array('name' => $publisher));

            if ($publisher_id) {
                $this->publication['publisher_id'] = $publisher_id->id;
            }
        }


        $this->publication['pub_type'] = $pub_type;

        if (isset($fields['title'])) {
            $this->publication['title'] = $fields['title'];
        }

        if (isset($fields['booktitle'])) {
            $this->publication['booktitle'] = $fields['booktitle'];
        }

        if (isset($fields['volume'])) {
            $this->publication['volume'] = $fields['volume'];
        }

        if (isset($fields['number'])) {
            $this->publication['number'] = $fields['number'];
        }

        if (isset($fields['chapter'])) {
            $this->publication['chapter'] = $fields['chapter'];
        }

        if (isset($fields['pages_start'])) {
            $this->publication['pages'] = $fields['pages_start'];
        }

        if (isset($fields['pages_end'])) {
            $this->publication['pages'] .= '-' . $fields['pages_end'];
        }

        if (isset($fields['pages'])) {
            $this->publication['pages'] = $fields['pages'];
        }

        if (isset($fields['series'])) {
            $this->publication['series'] = $fields['series'];
        }

        if (isset($fields['edition'])) {
            $this->publication['edition'] = $fields['edition'];
        }

        if (isset($fields['editor'])) {
            $this->publication['editor'] = $fields['editor'];
        }

        if (isset($fields['howpublished'])) {
            $this->publication['howpublished'] = $fields['howpublished'];
        }

        if (isset($fields['institution'])) {
            $this->publication['institution'] = $fields['institution'];
        }

        if (isset($fields['school'])) {
            $this->publication['school'] = $fields['school'];
        }

        if (isset($fields['organization'])) {
            $this->publication['organization'] = $fields['organization'];
        }

        if (isset($fields['type_of_report'])) {
            $this->publication['type_of_report'] = $fields['type_of_report'];
        }

        if (isset($fields['year'])) {
            $this->publication['issue_year'] = $fields['year'];
            if (isset($fields['month'])) {
                if (!is_numeric($fields['month'])) {
                    $month = $this->functions->strmonth2nummonth($fields['month']);
                } else {
                    $month = $fields['month'];
                }
            } elseif (!empty($fields['issue_date'])) {
                try {
                    $date = new \DateTime($fields['issue_date']);
                    $month = $date->format("m");
                } catch (\Exception $e) {
                    $month = null;
                }
            } else {
                $month = null;
            }
            if (intval($month)>0 && intval($month)<=12) {
                $this->publication['issue_month'] = intval($month);
            }
        }

        if (isset($fields['location'])) {
            $this->publication['location'] = $fields['location'];
        }

        if (isset($fields['address'])) { // neni to adresa publishera ????
            $this->publication['address'] = $fields['address'];
        }

        if (isset($fields['isbn'])) {
            $this->publication['isbn'] = $fields['isbn'];
        }

        if (isset($fields['note'])) {
            $this->publication['note'] = $fields['note'];
        }

        if (isset($fields['url'])) {
            $this->publication['url'] = $fields['url'];
        }

        if (isset($fields['abstract'])) {
            $this->publication['abstract'] = $fields['abstract'];
        }

        if (isset($fields['doi'])) {
            $this->publication['doi'] = $fields['doi'];
        }

        // NASTAV DO FORMULARU

        if ($pub_type == 'article' && !isset($this->publication['journal_id'])) {
            $journalForm = $this['journalCrud']['journalAddForm']; /** @var $journalForm \App\CrudComponents\Journal\JournalAddForm */
            $journalForm['name']->setDefaultValue(isset($journal) ? $journal : '');
            $journalForm['issn']->setDefaultValue(isset($issn) ? $issn : '');
        }

        if ($pub_type != 'inproceedings' && $pub_type != 'proceedings' && !isset($this->publication['publisher_id'])) {
            $publisherForm = $this['publisherCrud']['publisherAddForm']; /** @var $publisherForm \App\CrudComponents\Publisher\PublisherAddForm */
            $publisherForm['address']->setDefaultValue(isset($fields['address']) ? $fields['address'] : '');
            $publisherForm['name']->setDefaultValue(isset($fields['publisher']) ? $fields['publisher'] : '');
        }

        if ($pub_type == 'inproceedings' || $pub_type == 'proceedings') {
            if (!isset($this->publication['conference_year_id'])) {
                // todo
                // there is a problem of filling conference year, but not conference entity
                /*
                $conferenceYearForm = $this['conferenceYearCrud']['conferenceYearAddForm']; /** @var $conferenceYearForm \App\CrudComponents\ConferenceYear\ConferenceYearAddForm */
                /*
                $conferenceYearForm['name']->setDefaultValue(isset($conference) ? $conference : '');
                $conferenceYearForm['isbn']->setDefaultValue(isset($isbn) ? $isbn : '');
                $conferenceYearForm['location']->setDefaultValue(isset($location) ? $location : '');
                */
            }
        }

        $this['publicationAddNewForm']->setDefaults($this->publication);

        // NASTAV DO TEMPLATU

        if (isset($this->publication['conference_year_id'])) {
            $this->template->conferenceYearInfo = $selectedConferenceYear;
        }

        if (isset($this->publication['publisher_id']) && $this->publication['pub_type'] != 'inproceedings' && $this->publication['pub_type'] != 'proceedings') {
            // $this->template->publisherInfo_alone = $this->database->table('publisher')->get($this->publication['publisher_id']);
            $this->template->publisherInfo_alone = $this->publisherModel->find($this->publication['publisher_id']);
        }

        if (isset($this->publication['journal_id'])) {
            $this->template->journalInfo = $this->journalModel->find($this->publication['journal_id']);
        }

        if (isset($this->publication['conference'])) {
            $this->template->conferenceInfo = $this->conferenceModel->find($this->publication['conference']);
        }

        $this->template->definition = $definitionTemplate;

        $this->flashMessage('Operation has been completed successfully.', 'alert-success');
    }

    public function actionAddNew($id) {

        // rozlisovat ADD, EDIT (id), smazat vsechny kategorie a vytvorit nove!!, u EDIT skryt IMPORT

        Debugger::fireLog('actionAddNew()');
        $params = $this->getHttpRequest()->getQuery();
        $this->publicationId = $id;
        Debugger::fireLog($params);

        $this->publication = array();

        if ($id) {
            $this->publication = $this->publicationModel->find($id)->toArray();
            $title = "Edit Publication";

            if (!$this->publication) {
                $this->error('Publication not found');
            }

            if (!$this->publication['publisher_id']) {
                unset($this->publication['publisher_id']);
            }

            if (!$this->publication['journal_id']) {
                unset($this->publication['journal_id']);
            }

            if (!$this->publication['conference_year_id']) {
                unset($this->publication['conference_year_id']);
            }
        } else {
            $title = "Add new Publication";
        }

        if (isset($params['do']) && $id && $params['do'] == "changeConferenceYear") {
            unset($this->publication['conference_year_id']);
        }


        $this->loadJournals();
        $this->loadPublishers();

        $this->loadConferences();

        $this->conferenceYears = array();

        $this->authors = $this->authorModel->getAuthorsNames();

        $this->loadAttributes();

        $this->group = $this->groupModel->findAll()->order('name ASC');

        // ===========================================
        $selectedGroups = array();
        $selectedAuthors = array();
        $files = array();

        if ($id) {

            $selectedAuthors = $this->authorModel->getAuthorsNamesByPubId($id);
            $selectedConferenceYear = false;
            $this->attribStorage = $this->attribStorageModel->findAllBy(array("publication_id" => $id));
            $selectedGroups = $this->groupHasPublicationModel->findAllBy(array('publication_id' => $id));

            if (isset($this->publication['conference_year_id'])) {
                $selectedConferenceYear = $this->conferenceYearModel->find($this->publication['conference_year_id']);
                $this->selectedConferenceId = $selectedConferenceYear->conference_id;
                $this->publication['conference'] = $selectedConferenceYear->conference_id;
                $this->loadConferenceYears();
            }

            $selectedCategories = $this->categoriesHasPublicationModel->findAllBy(array('publication_id' => $id));
            $selCatIds = array();
            foreach($selectedCategories as $selectedCategory) $selCatIds[] = $selectedCategory->id;
            $this['publicationAddNewForm']['categories']->setDefaultValue(implode(' ', $selCatIds));

        }

        // ===========================================

        $this->template->selectedAuthors = $selectedAuthors;
        $this->template->selectedGroups = $selectedGroups;
        if (!$id) {
            $this->template->files = $files;
        }
        $this->template->fileDeleted = false;
        //
        $this->template->publication = $this->publication;
        $this->template->authors = $this->authors;
        $this->template->title = $title;
        //
        $this->template->attributeInfo = false;

        $this->template->groupTree = $this->group;
        //

        if ($id) {
            $this['publicationAddNewForm']->setDefaults($this->publication);

            if ($selectedConferenceYear) {
                $this->template->conferenceYearInfo = $selectedConferenceYear;
                if($selectedConferenceYear->publisher_id)
                    $this->template->conferenceYearPublisherInfo = $selectedConferenceYear->ref('publisher');
            }

            if (isset($this->publication['publisher_id']) && $this->publication['pub_type'] != 'inproceedings' && $this->publication['pub_type'] != 'proceedings') {
                $this->template->publisherInfo_alone = $this->publisherModel->find($this->publication['publisher_id']);
            }

            if (isset($this->publication['journal_id'])) {
                $this->template->journalInfo = $this->journalModel->find($this->publication['journal_id']);
            }

            if (isset($this->publication['conference'])) {
                $this->template->conferenceInfo = $this->conferenceModel->find($this->publication['conference']);
            }
        }
        $this->template->_form = $this['publicationAddNewForm'];

        $this->template->id = $id;
    }

    public function renderAddNew() {
        if (!isset($this->template->files) && $this->publicationId) {
            $this->template->files = $this->filesModel->prepareFiles($this->publicationId);
        }

        $this->template->selectedPublisherId = $this->selectedPublisherId;
        $this->template->publisherInfo = !$this->selectedPublisherId ? null :
                $this->publisherModel->find($this->selectedPublisherId);

        $this->template->selectedJournalId = $this->selectedJournalId;
        $this->template->journalInfo = !$this->selectedJournalId ? null :
            $this->journalModel->find($this->selectedJournalId);

        $this->template->selectedConferenceId = $this->selectedConferenceId;
        $this->template->conferenceInfo = !$this->selectedConferenceId ? null :
            $this->conferenceModel->find($this->selectedConferenceId);

        $this->template->selectedConferenceYearId = $this->selectedConferenceYearId;
        $this->template->conferenceYearInfo = !$this->selectedConferenceYearId ? null :
            $this->conferenceYearModel->find($this->selectedConferenceYearId);

        $this->template->selectedGroupId = !$this->selectedGroupId ? null : $this->selectedGroupId;
        
        $this->template->selectedAuthorId = !$this->selectedAuthorId ? null : $this->selectedAuthorId;

        $this->template->attributes = $this->attributes;

        if(!isset($this->template->authorDeleted)) $this->template->authorDeleted = false;

        if(!isset($this->template->groupDeleted)) $this->template->groupDeleted = false;
        if(!isset($this->template->groupEdited)) $this->template->groupEdited = false;
        if(!isset($this->template->groupAdded)) $this->template->groupAdded = false;

        if(!isset($this->template->authorDeleted)) $this->template->authorDeleted = false;
        if(!isset($this->template->authorEdited)) $this->template->authorEdited = false;
        if(!isset($this->template->authorAdded)) $this->template->authorAdded = false;
    }

    protected function createComponentConferenceYearCrud() {
        $c = $this->conferenceYearCrudFactory->create($this->selectedConferenceId ? $this->selectedConferenceId : 0);
        $c->onAdd[] = function($row){
            $this->loadConferenceYears(true);
            $this->redrawControl('publicationAddNewForm-conference_year_id');
            $this['publicationAddNewForm']['conference_year_id']->setValue($row->id);
            $this->handleShowConferenceYearInfo($row->id);
            $this->redrawControl('conferenceYearInfo');
        };
        $c->onEdit[] = function($row) {
            $this->loadConferenceYears(true);
            $this->redrawControl('publicationAddNewForm-conference_year_id');
            $this->selectedConferenceId = $row->conference_id;
            $this->loadConferenceYears(true);
            $this['publicationAddNewForm']['conference_year_id']->value = $row->id;
            $this->handleShowConferenceYearInfo($row->id);
            $this->redrawControl('conferenceYearInfo');
        };
        $c->onDelete[] = function($row) {
            $this->loadConferenceYears(true);
            $this->redrawControl('publicationAddNewForm-conference_year_id');
            $this['publicationAddNewForm']['conference_year_id']->setValue(null);
            $this->handleShowConferenceYearInfo(0);
            $this->redrawControl('conferenceYearInfo');
        };
        return $c;
    }

    public function createComponentPublicationAddNewForm($name) {
        if(!$this->journals) $this->loadJournals();
        if(!$this->publishers) $this->loadPublishers();
        if(!$this->conferences) $this->loadConferences();
        if(!$this->conferenceYears) $this->loadConferenceYears();
        if(!$this->attributes) $this->loadAttributes();

        $form = new \PublicationAddNewForm($this->publicationId, $this->types, $this->attribStorage,
            $this->publishers, $this->journals, $this->conferences, $this->conferenceYears, $this->attributes,
            $this->publicationModel, $this, $name);

        $form->onSuccess[] = array($this, "publicationAddNewFormSucceeded");

        return $form;
    }


    public function publicationAddNewFormSucceeded($form) {

        $this->publicationModel->beginTransaction();

        $documentsObjectCreatedId = null;

        try {
            $data = $form->getValuesTransformed();
            $formValues = $form->getValuesTransformed();

            $values = $form->getHttpData();
            $formValues['conference_year_id'] = $values['conference_year_id'];

            Debugger::fireLog('publicationAddNewFormSucceeded()');

            unset($formValues['categories']);
            unset($formValues['group']);
            unset($formValues['authors']);
            unset($formValues['conference']);
            unset($formValues['upload']);
            unset($formValues['id']);

            $formValues['submitter_id'] = $this->user->id;

            if ($this->user->isInRole('admin')) {
                $formValues['confirmed'] = 1;
            } else {
                $formValues['confirmed'] = 0;
            }

            $formValues = $this->publicationModel->prepareFormData($formValues);

            foreach ($this->attributes as $attrib) {
                unset($formValues['attributes_' . $attrib->id]);
            }

            if ($form->values->id) {
                $formValues['id'] = $form->values->id;
                $record = $this->publicationModel->update($formValues);
                $record = $this->publicationModel->find($form->values->id);
            } else {
                $record = $this->publicationModel->insert($formValues);
            }


            if ($form->values->id) {
                $authorHasPublication = $this->authorHasPublicationModel->findAllBy(array("publication_id" => $form->values->id));
                foreach ($authorHasPublication as $item) {
                    $item->delete();
                }

                $categoriesHasPublication = $this->categoriesHasPublicationModel->findAllBy(array("publication_id" => $form->values->id));
                foreach ($categoriesHasPublication as $item) {
                    $item->delete();
                }

                $groupHasPublication = $this->groupHasPublicationModel->findAllBy(array("publication_id" => $form->values->id));
                foreach ($groupHasPublication as $item) {
                    $item->delete();
                }
            }

            // extrahuj informace z pdf
            // Předáme data do šablony
            $this->template->values = $data;

            // $queueId = uniqid();
            // Přesumene uploadované soubory


            foreach ($form->values->upload as $file) {
                $extractedText = '';
                // $file je instance HttpUploadedFile
                // $newFilePath = \Nette\Environment::expand("%appDir%") . "/../storage/q{" . $queueId . "}__f{" . rand(10, 99) . "}__" . $file->getName();
                $newDirPath = $this->dirPath . $record->id;
                $newFilePath = $newDirPath . "/" . rand(10, 99) . "_" . $file->getName();

                $file->move($newFilePath);
                //  $extension = $this->getFileExtension($file->getName());
                $extension = $this->filesModel->getFileExtension($file->getName());

                switch ($extension) {
                    case "pdf":
                        $this->pdfParser = new \Smalot\PdfParser\Parser();
                        $extractedText = $this->pdfExtractorFirstPage($newFilePath);
                        break;
                    case "ps":
                        // TODOOOO
                        // $extractedText = $this->psExtractor($newFilePath);
                        break;
                    case "doc":
                        // TODOOOO
                        // $extractedText = $this->docExtractor($newFilePath);
                        break;
                    case "txt":
                        // TODOOOO
                        // $extractedText = $this->txtExtractor($newFilePath);
                        break;
                }


                $this->fulltext .= "--- " . $file->getName() . " ---\n" . $extractedText . "\n\n\n";
            }


            if ($form->values->id) {
                $document = $this->documentsModel->find($form->values->id);
            }
            if (!empty($document)) {
                        $doc_a = $document->toArray();
                        $doc_a['title'] = $form->values->title;
                        $doc_a['content'] .= $this->fulltext;
                        $this->documentsModel->find($form->values->id)->update($doc_a);
            } else {
                $this->documentsModel->insert(array(
                    'publication_id' => $record->id,
                    'title' => $form->values->title,
                    'content' => $this->fulltext
                ));
                $documentsObjectCreatedId = $record->id;
            }

            $authors = array();
            $categories = array();
            $group = array();

            if (!empty($form->values->authors) || $form->values->authors == "0") {
                $authors = explode(" ", $form->values->authors);
            }
            if (!empty($form->values->categories) || $form->values->categories == "0") {
                $categories = explode(" ", $form->values->categories);
            }
            if (!empty($form->values->group) || $form->values->group == "0") {
                $group = explode(" ", $form->values->group);
            }

            foreach ($categories as $key => $value) {
                $this->categoriesHasPublicationModel->insert(array(
                    'categories_id' => $value,
                    'publication_id' => $record->id
                ));
            }

            foreach ($group as $key => $value) {
                $this->groupHasPublicationModel->insert(array(
                    'group_id' => $value,
                    'publication_id' => $record->id
                ));
            }


            if ($form->values->pub_type != "proceedings") {
                foreach ($authors as $key => $value) {
                    $this->authorHasPublicationModel->insert(array(
                        'author_id' => $value,
                        'publication_id' => $record->id,
                        'priority' => $key
                    ));
                }
            }

            // ATTRIBUTES

            if ($form->values->id) {
                $attribStorage = $this->attribStorageModel->findAllBy(array("publication_id" => $form->values->id));
                foreach ($attribStorage as $item) {
                    $item->delete();
                }
            }

            foreach ($this->attributes as $attrib) {
                if (!empty($values['attributes_' . $attrib->id])) {
                    $this->attribStorageModel->insert(array(
                        'publication_id' => $record->id,
                        'attributes_id' => $attrib->id,
                        'submitter_id' => $this->user->id,
                        'value' => $values['attributes_' . $attrib->id],
                    ));
                }
            }

            $this->publicationModel->commitTransaction();

            $this->flashMessage('Operation has been completed successfullly.', 'alert-success');

        } catch (\Exception $e) {
            $this->publicationModel->rollbackTransaction();
            if($documentsObjectCreatedId)
                $this->documentsModel->delete($documentsObjectCreatedId);
            throw $e;
        }

        if (!$this->isAjax()) {
            $this->presenter->redirect('Publication:showpub', $record->id);
        } else {
            $this->invalidateControl();
        }
    }

    public function pdfExtractor($newFilePath) {
        $pdf = $this->pdfParser->parseFile($newFilePath);
        $text = $pdf->getText();
        return $text;
    }

    public function pdfExtractorFirstPage($newFilePath) {
        $pdf = $this->pdfParser->parseFile($newFilePath);
        $pages = $pdf->getPages();
        return $pages[0]->getText();
    }

    public function handleDeleteFile($fileId) {

        Debugger::fireLog("handleDeleteFile($fileId)");

        $files = $this->filesModel->prepareFiles($this->publicationId);

        if (is_file($files[$fileId]['path'])) {
            unlink($files[$fileId]['path']);
        }

        $this->template->fileDeleted = true;
        $this->flashMessage('Operation has been completed successfully.', 'alert-success');

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl('files');
            $this->redrawControl('deleteFile');
            $this->redrawControl('flashMessages');
        }
    }

    public function actionShowAll($sort, $order, $keywords, $filter) {
        $this->drawAllowed = true;
        $this->template->publicationDeleted = false;
    }

    public function renderShowAll() {
        if ($this->drawAllowed) {
            $this->drawPublications($this['individualFilter']->getActiveButtonName() == 'starred');
        }
    }

    public function drawPublications($starred = false) {
        Debugger::fireLog('drawPublications');
        $params = $this->getHttpRequest()->getQuery();
        $alphabet = range('A', 'Z');

        if (!isset($params['sort'])) {
            $params['sort'] = 'title';
        }

        if (!isset($params['order'])) {
            $params['order'] = 'ASC';
        }

        if (!isset($params['filter']) || $params['filter'] == 'none') {
            $params['filter'] = 'none';
        }

        if (!isset($params['keywords'])) {
            $params['keywords'] = '';
        }

        if (!isset($this->template->records)) {
            $this->records = $this->publicationModel->findAllByKw($params);

            if($starred) {
                $this->records->where(':submitter_has_publication.submitter_id = ?', $this->user->id);
            }

            $this->setupRecordsPaginator();

            $this->template->records = $this->records;
            $this->data = $params;

            if (isset($params['sort'])) {
                $this->template->sort = $params['sort'];
            } else {
                $this->template->sort = null;
            }

            if (isset($params['order'])) {
                $this->template->order = $params['order'];
            } else {
                $this->template->order = null;
            }

            if (isset($params['keywords'])) {
                $keywords = $params['keywords'];
            }

            if (isset($params['filter'])) {
                $filter = $params['filter'];
            }

            $params = array();

            if (isset($keywords)) {
                $params['keywords'] = $keywords;
            }

            if (isset($filter)) {
                $params['filter'] = $filter;
            }

            $this->template->filter = $filter;
            $this->template->alphabet = $alphabet;

            $this->template->params = $params;
        }

        $authorsByPubId = array();
        foreach($this->template->records as $rec) {
            /** @var $rec Nette\Database\Table\ActiveRow */
            foreach($rec->related('author_has_publication')->order('priority ASC') as $authHasPub) {
                $author = $authHasPub->ref('author');
                if(!isset($authorsByPubId[$rec->id])) $authorsByPubId[$rec->id] = [];
                $authorsByPubId[$rec->id][] = $author;
            }
        }
        $this->template->authorsByPubId = $authorsByPubId;

        $this->redrawControl('publicationShowAll');
    }

    protected function createComponentPublicationShowAllSearchForm($name) {
        $form = new \PublicationShowAllSearchForm($this, $name);
        $form->onSuccess[] = $this->publicationShowAllSearchFormSucceeded;
        return $form;
    }

    public function publicationShowAllSearchFormSucceeded($form) {
        $formValues = $form->getValues();
    }

    public function actionShowPub($id) {
        $this->publication = $this->publicationModel->find($id);

        if (!$this->publication) {
            $this->error('Publication not found');
        }

        $this->publicationId = $id;

        $data = $this->publicationModel->getAllPubInfo($this->publication, $this->authorModel, $this->functions, $this->filesModel, $this->user->id, $this->user->isInRole('admin'));

        $publication = $data['publication'];

        // bibtex
        $bibtex = Helpers\Bibtex::create($publication['pub_type']);
        $bibtexDefinition = $bibtex->get_bibtex($publication);

        // endnote
        $endnote = new Helpers\EndNote($publication);
        $endnote->createDefinition();
        $endnoteDefinition = $endnote->getDefinition();

        // refworks
        $refworks = new Helpers\RefWorks($publication);
        $refworks->createDefinition();
        $refworksDefinition = $refworks->getDefinition();


        $this->template->bibtexDefinition = $bibtexDefinition;
        $this->template->endnoteDefinition = $endnoteDefinition;
        $this->template->refworksDefinition = $refworksDefinition;

        $this->template->attributes = $data['attributes'];
        $this->template->publication = $this->publication;
        $this->template->journal = $data['journal'];
        $this->template->categories = $data['categories'];
        $this->template->group = $data['group'];
        $this->template->authors = $data['authors'];
        $this->template->publisher = $data['publisher'];
        $this->template->favourite = $data['favourite'];
        $this->template->annotations = $data['annotations'];
        $this->template->references = $data['references'];
        $this->template->citations = $data['citations'];
        $this->template->conferenceYear = $data['conferenceYear'];
        $this->template->conferenceYearPublisher = $data['conferenceYearPublisher'];
        $this->template->files = $data['files'];
        $this->template->annotationAdded = $data['annotationAdded'];
        $this->template->annotationEdited = $data['annotationEdited'];
        $this->template->annotationDeleted = $data['annotationDeleted'];
        $this->template->pubCit = $data['pubCit'];
        $this->template->pubCit['author_array'] = $data['pubCit_author_array'];
        $this->template->pubCit['author'] = $data['pubCit_author'];
        $this->template->types = $this->types;
        $this->template->referenceDeleted = false;
        
        $authorsByPubId = array();
        foreach($this->template->references as $rec) {
            /** @var $rec Nette\Database\Table\ActiveRow */
            foreach($rec->reference->related('author_has_publication')->order('priority ASC') as $authHasPub) {
                $author = $authHasPub->ref('author');
                if(!isset($authorsByPubId[$rec->reference->id])) $authorsByPubId[$rec->reference->id] = [];
                $authorsByPubId[$rec->reference->id][] = $author;
            }
        }
        foreach($this->template->citations as $rec) {
            /** @var $rec Nette\Database\Table\ActiveRow */
            foreach($rec->publication->related('author_has_publication')->order('priority ASC') as $authHasPub) {
                $author = $authHasPub->ref('author');
                if(!isset($authorsByPubId[$rec->publication->id])) $authorsByPubId[$rec->publication->id] = [];
                $authorsByPubId[$rec->publication->id][] = $author;
            }
        }
        $this->template->authorsByPubId = $authorsByPubId;
    }

    public function renderShowPub() {
        $_this = $this;
        $this->template->registerHelper('template', function($text) use ($_this) {
            $template = new Nette\Templating\Template();
            $template->control = $template->_control = $_this;
            $template->presenter = $template->_presenter = $_this->getPresenter(FALSE);
            $template->registerFilter(new Nette\Latte\Engine);
            // dodané proměnné
            $template->user = $_this->user;
            $template->baseUri = $_this->template->baseUri;
            $template->basePath = $_this->template->basePath;

            $template->pubCit = $_this->template->pubCit;
            $template->pubCit['author_array'] = $_this->template->pubCit['author_array'];
            $template->pubCit['author'] = $_this->template->pubCit['author'];
            // flash message
            $presenter = $_this->getPresenter(FALSE);
            if ($presenter->hasFlashSession()) {
                $id = $_this->getParameterId('flash');
                $template->flashes = $presenter->getFlashSession()->$id;
            } else {
                $template->flashes = array();
            }
            $template->setSource($text);
            $txt = $template->__toString();
            return $txt;
        });

        $this->template->formats = $this->formatModel->findAll();
    }

    // ====================================================================
    // ==================== P U B L I C A T I O N =========================
    // ====================================================================


    public function handleDeletePublication($publicationId) {

        Debugger::fireLog('handleDeletePublication(' . $publicationId . ')');

        $this->drawAllowed = true;

        // $publication = $this->context->Database->database->table('publication')->get($publicationId);
        $publication = $this->publicationModel->find($publicationId);

        if (!$publication) {
            $this->error('Publication not found');
        }

        $this->filesModel->deleteFiles($publicationId);
        $this->publicationModel->deleteAssociatedRecords($publicationId);


        $this->template->publicationInfo = false;
        $this->template->publicationDeleted = true;
        // $this->template->publicationId = $publicationId;
        $this->flashMessage('Operation has been completed successfully.', 'alert-success');

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('Publication:showall');
        } else {
            $this->redrawControl('deletePublication');
            $this->redrawControl('publicationShowAllRecords');
            $this->redrawControl('flashMessages');
        }
    }
    
    public function handleDeleteReference($referenceId) {
         $this->drawAllowed = true;

        $reference = $this->referenceModel->find($referenceId);

        if (!$reference) {
            $this->error('Reference not found');
        }
        
        $this->referenceModel->delete($referenceId);

        $this->template->referenceDeleted = true;

        if (!$this->presenter->isAjax()) {
            $this->flashMessage('Operation has been completed successfully.', 'alert-success');
            $this->presenter->redirect('Publication:showall');
        } else {
            $this->redrawControl('deleteReference');
            $this->redrawControl('referenceShowAllRecords');
        }
        
    }

    public function handleSetFavouritePub($favorite_id) {

        Debugger::fireLog('handleSetFavouritePub(' . $favorite_id . ')');

        $this->submitterHasPublicationModel->insert(array(
            'publication_id' => $favorite_id,
            'submitter_id' => $this->user->id
        ));


        if (!$this->presenter->isAjax()) {
            $this->flashMessage('Operation has been completed successfully.', 'alert-success');
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl();
        }
    }

    public function handleUnsetFavouritePub($favorite_id) {

        $record = $this->submitterHasPublicationModel->findOneBy(array(
            'publication_id' => $favorite_id,
            'submitter_id' => $this->user->id));

        if ($record) {
            $record->delete();
        }

        if (!$this->presenter->isAjax()) {
            $this->flashMessage('Operation has been completed successfully.', 'alert-success');
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl();
        }
    }

    // new


    // --

    protected function createComponentPublicationCategoryList(){
        $c = $this->publicationCategoryListFactory->create();
        $c->setIsSelectable(true);
        $c->setHasThreeStates(false);
        $c->setHasControls(true);
        return $c;
    }

    public function createComponentPublisherCrud(){
        $publisherCrud  = $this->publisherCrudFactory->create();


        $publisherCrud->onAdd[] = function($record) {
            $this->handleShowPublisherInfo($record->id);
            $this->loadPublishers(true);
            $this["publicationAddNewForm"]["publisher_id"]->setValue($record->id);
            $this->selectedPublisherId = $record->id;
            $this->redrawControl('publicationAddNewForm-publisher_id');
            $this->redrawControl('publisherControls');
            $this->redrawControl('publisherInfo');
        };

        $publisherCrud->onDelete[] = function($record) {
            $this->handleShowPublisherInfo($record->id);
            $this->loadPublishers(true);
            $this["publicationAddNewForm"]["publisher_id"]->setValue(null);
            $this->selectedPublisherId = null;
            $this->redrawControl('publicationAddNewForm-publisher_id');
            $this->redrawControl('publisherControls');
            $this->redrawControl('publisherInfo');
        };

        $publisherCrud->onEdit[] = function($record)  {
            $this->handleShowPublisherInfo($record->id);
            $this->loadPublishers(true);
            $this["publicationAddNewForm"]["publisher_id"]->setValue($record->id);
            $this->redrawControl('publicationAddNewForm-publisher_id');
            $this->redrawControl('publisherInfo');
        };

        return $publisherCrud;
    }


    public function createComponentJournalCrud(){
        $journalCrud  = $this->journalCrudFactory->create();

        $journalCrud->onAdd[] = function($record) {
            $this->handleShowJournalInfo($record->id);
            $this->loadJournals(true);
            $this["publicationAddNewForm"]["journal_id"]->setValue($record->id);
            $this->selectedJournalId = $record->id;
            $this->redrawControl('publicationAddNewForm-journal_id');
            $this->redrawControl('journalControls');
            $this->redrawControl('journalInfo');
        };

        $journalCrud->onDelete[] = function($record) {
            $this->handleShowJournalInfo($record->id);
            $this->loadJournals(true);
            $this["publicationAddNewForm"]["journal_id"]->setValue(null);
            $this->selectedJournalId = null;
            $this->redrawControl('publicationAddNewForm-journal_id');
            $this->redrawControl('journalControls');
            $this->redrawControl('journalInfo');
        };

        $journalCrud->onEdit[] = function($record)  {
            $this->handleShowJournalInfo($record->id);
            $this->loadJournals(true);
            $this["publicationAddNewForm"]["journal_id"]->setValue($record->id);
            $this->redrawControl('publicationAddNewForm-journal_id');
            $this->redrawControl('journalInfo');
        };

        return $journalCrud;
    }


    public function createComponentConferenceCrud(){
        $conferenceCrud  = $this->conferenceCrudFactory->create();

        $conferenceCrud->onAdd[] = function($record) {
            $this->handleShowConferenceInfo($record->id);
            $this->loadConferences(true);
            $this["publicationAddNewForm"]["conference"]->setValue($record->id);
            $this->selectedConferenceId = $record->id;
            $this->redrawControl('publicationAddNewForm-conference');
            $this->redrawControl('publicationAddNewForm-conference_year_id');
            $this->redrawControl('conferenceControls');
            $this->redrawControl('conferenceInfo');
        };

        $conferenceCrud->onDelete[] = function($record) {
            $this->handleShowConferenceInfo($record->id);
            $this->loadConferences(true);
            $this["publicationAddNewForm"]["conference"]->setValue(null);
            $this->selectedConferenceId = null;
            $this->redrawControl('publicationAddNewForm-conference');
            $this->redrawControl('publicationAddNewForm-conference_year_id');
            $this->redrawControl('conferenceControls');
            $this->redrawControl('conferenceInfo');
        };

        $conferenceCrud->onEdit[] = function($record)  {
            $this->handleShowConferenceInfo($record->id);
            $this->loadConferences(true);
            $this["publicationAddNewForm"]["conference"]->setValue($record->id);
            $this->redrawControl('publicationAddNewForm-conference');
            $this->redrawControl('conferenceInfo');
        };

        $conferenceCrud->onCreateConferenceYearCrud[] = function(\App\CrudComponents\ConferenceYear\ConferenceYearCrud &$c) {

            $fnRedraw = function() use ($c) {
                $this->selectedConferenceId = $c->getConferenceId();

                $this->loadConferenceYears(true);
                $this->redrawControl('publicationAddNewForm-conference_year_id');
                $this->redrawControl('conferenceYearInfo');
            };

            $c->onEdit[] = $fnRedraw;
            $c->onAdd[] = $fnRedraw;
            $c->onDelete[] = $fnRedraw;
        };

        return $conferenceCrud;
    }

    public function createComponentAttributeCrud(){
        $attributeCrud  = $this->attributeCrudFactory->create();

        $attributeCrud->onAdd[] = function($record) {
            $this->loadAttributes(true);
            $this->redrawControl('attributeShowAllRecords');
        };

        $attributeCrud->onDelete[] = function($record) {
            $this->loadAttributes(true);
            $this->redrawControl('attributeShowAllRecords');
        };

        $attributeCrud->onEdit[] = function($record)  {
            $this->loadAttributes(true);
            $this->redrawControl('attributeShowAllRecords');
        };

        return $attributeCrud;
    }

    public function createComponentGroupCrud(){
        $c = $this->groupCrudFactory->create();

        $c->onAdd[] = function($record) {
            $this->template->groupAdded = $record;
            $this->redrawControl('groupAdded');
        };

        $c->onEdit[] = function($record) {
            $this->template->groupEdited = $record;
            $this->redrawControl('groupEdited');
        };

        $c->onDelete[] = function($record) {
            $this->template->groupDeleted = $record;
            $this->redrawControl('groupDeleted');
        };

        return $c;
    }

    public function createComponentAuthorCrud() {

        $c = $this->authorCrudFactory->create();

        $c->onAdd[] = function($record) {
            $this->template->authorAdded = $record;
            $this->redrawControl('authorAdded');
        };

        $c->onEdit[] = function($record) {
            $this->template->authorEdited = $record;
            $this->redrawControl('authorEdited');
        };

        $c->onDelete[] = function($record) {
            $this->template->authorDeleted = $record;
            $this->redrawControl('authorDeleted');
        };

        return $c;
    }


    // =======================   DATA LOADERS    =======================
    
    protected function loadPublishers($updateDependencies = false){
        $this->publishers =  $this->publisherModel->findAll()->order("name ASC")->fetchPairs('id', 'name');
        if($updateDependencies) {
            if(isset($this['publicationAddNewForm'])) $this['publicationAddNewForm']['publisher_id']->setItems($this->publishers);
        }
    }

    protected function loadJournals($updateDependencies = false){
        $this->journals =  $this->journalModel->findAll()->order("name ASC")->fetchPairs('id', 'name');
        if($updateDependencies) {
            if(isset($this['publicationAddNewForm'])) $this['publicationAddNewForm']['journal_id']->setItems($this->journals);
        }
    }

    protected function loadConferences($updateDependencies = false){
        $this->conferences = $this->conferenceModel->getConferenceForSelectbox();
        if($updateDependencies) {
            if(isset($this['publicationAddNewForm'])) $this['publicationAddNewForm']['conference']->setItems($this->conferences);
        }
    }

    protected function loadConferenceYears($updateDependencies = false){
        if($this->selectedConferenceId) {
            $this->conferenceYears = $this->conferenceYearModel->getConferenceYearForSelectbox($this->selectedConferenceId);
            if ($updateDependencies) {
                if (isset($this['publicationAddNewForm'])) $this['publicationAddNewForm']['conference_year_id']->setItems($this->conferenceYears);
            }
        } else $this->conferenceYears = [];
    }

    protected function loadAttributes($updateDependencies = false){
        $this->attributes =  $this->attributeModel->findAll()->order("name ASC");
        if($updateDependencies) {
            if(isset($this['publicationAddNewForm'])) $this['publicationAddNewForm']->setAttributes($this->attributes);
        }
    }
    


    // ==============================       SIGNALS        ===============================

    public function handleShowPublisherInfo($publisherId) {

        $this->drawAllowed = false;

        $this->selectedPublisherId = $publisherId;

        if (!$this->isAjax()) {
            $this->redirect('this');
        } else {
            $this->redrawControl('publisherInfo');
            $this->redrawControl('publisherControls');
        }
    }

    public function handleShowJournalInfo($journalId) {

        $this->drawAllowed = false;

        $this->selectedJournalId = $journalId;

        if (!$this->isAjax()) {
            $this->redirect('this');
        } else {
            $this->redrawControl('journalInfo');
            $this->redrawControl('journalControls');
        }
    }

    public function handleShowConferenceInfo($conferenceId) {
        $this->drawAllowed = false;

        $this->selectedConferenceId = $conferenceId;

        $this->selectedConferenceYearId = null;

        $this->loadConferenceYears(true);

        if (!$this->isAjax()) {
            $this->redirect('this');
        } else {
            $this->redrawControl('conferenceYearCrud');
            $this->redrawControl('conferenceInfo');
            $this->redrawControl('conferenceControls');
            $this->redrawControl('conferenceYearInfo');
            $this->redrawControl('conferenceYearControls');
            $this->redrawControl('publicationAddNewForm-conference_year_id');
        }
    }

    public function handleShowConferenceYearInfo($conferenceYearId) {
        $this->drawAllowed = false;

        $this->selectedConferenceYearId = $conferenceYearId;
        
        $conferenceYearInfo =  $this->conferenceYearModel->find($conferenceYearId);
        
        $this['publicationAddNewForm']['issue_year']->setValue($conferenceYearInfo['w_year']);
        $this['publicationAddNewForm']['issue_month']->setValue($conferenceYearInfo['w_from']->format("n"));
        
        if (!$this->isAjax()) {
            $this->redirect('this');
        } else {
            $this->redrawControl('conferenceYearInfo');
            $this->redrawControl('conferenceYearControls');
            $this->redrawControl('issueDate');
        }
    }

    public function handleSelectGroup($groupId) {
        $this->drawAllowed = false;
        $this->selectedGroupId = $groupId;

        if (!$this->isAjax()) {
            $this->redirect('this');
        } else {
            $this->redrawControl('groupControls');
        }
    }

    public function handleSelectAuthor($authorId) {
        $this->drawAllowed = false;
        $this->selectedAuthorId = $authorId;

        if (!$this->isAjax()) {
            $this->redirect('this');
        } else {
            $this->redrawControl('authorControls');
        }
    }

    // moved from basepresenter (what a mess!)

    public function handleSetConfirmed($id) {

        $this->publicationModel->update(array('id' => $id, 'confirmed' => 1));

        $this->flashMessage('Operation has been completed successfully.', 'alert-success');

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl();
        }
    }

    public function handleSetUnConfirmed($id) {

        $this->publicationModel->update(array('id' => $id, 'confirmed' => 0));

        $this->flashMessage('Operation has been completed successfully.', 'alert-success');

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl();
        }
    }

    protected function createComponentPublicationSpringerForm($name) {
        $form = new \PublicationSpringerForm($this, $name);
        $form->onSuccess[] = $this->publicationSpringerFormSucceeded;
        return $form;
    }

    public function publicationSpringerFormSucceeded($form) {

        Debugger::fireLog('publicationSpringerFormSucceeded');

        $values = $form->getHttpData();

        if (!array_key_exists("data_springer", $values)) {
            $this->presenter->redirect('this');
        }


        $data = $this->springerService->fetchData($values['id_springer'], $values['type_springer'], false);
        $dataArr = $data['array'];
        $data = $data['object'];

        if ($data) {

            $this->publication = $this->springerService->parseData($data[$values['data_springer']]);


            $result = array();
            foreach ($this->publication['creators'] as $author) {
                $parser = new Helpers\HumanNameParser_Parser($bodytag = str_replace(".", ". ", $author->creator));
                $result[] = array('name' => $parser->getFirst(), 'middlename' => $parser->getMiddle(), 'surname' => $parser->getLast());
            }


            $selectedAuthors = array();

            foreach ($result as $author) {
                $tempAuthor = $this->authorModel->getAuthorNameByAuthorName($author['name'], $author['middlename'], $author['surname']);
                if ($tempAuthor) {
                    $selectedAuthors[$tempAuthor['id']] = $tempAuthor['name'];
                }
            }

            $this->template->selectedAuthors = $selectedAuthors;

            unset($this->publication['creators']);
        }

        $this['publicationAddNewForm']->setDefaults($this->publication);


        $this->flashMessage('Operation has been completed successfully.', 'alert-success');
    }

    public function handleFetchFromSpringer($idSpringer, $typeSpringer) {

        Debugger::fireLog("handleFetchFromSpringer($idSpringer, $typeSpringer)");

        $radioArray = $this->springerService->fetchData($idSpringer, $typeSpringer);

        if ($radioArray && count($radioArray)) {
            $this["publicationSpringerForm"]["data_springer"]->setItems($radioArray); // set up new values
            $this->flashMessage('Operation has been completed successfully.', 'alert-success');
            $this->template->springerMessage = array('status' => 'success', 'message' => 'Data fetched successfully, please select specific data for importing from RadioBox above!');
        } else {
            $this["publicationSpringerForm"]["data_springer"]->setItems(array()); // set up new values
            $this->template->springerMessage = array('status' => 'danger', 'message' => 'No data found, please try again with different input!');
        }


        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl('publicationSpringerForm-data');
        }
    }

    public function createComponentIndividualFilter ( ) {
        $c = new \App\Components\ButtonToggle\ButtonGroupComponent([
            'all'     =>  array(
                'caption'   =>  'All publications',
                'icon'      =>  'list'
            ),
            'starred'      =>  array(
                'caption'   =>  'Starred publications',
                'icon'      =>  'star'
            ),
        ], 'all');

        $c->onActiveButtonChanged[] = function(){
            $this->resetPagination();
        };

        return $c;
    }


}
