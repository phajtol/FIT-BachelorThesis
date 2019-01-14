<?php

namespace App\Presenters;

use App\Components\AlphabetFilter\AlphabetFilterComponent;
use App\Components\ButtonToggle\ButtonGroupComponent;
use App\Components\PublicationCategoryList\PublicationCategoryListComponent;
use App\CrudComponents\Annotation\AnnotationCrud;
use App\CrudComponents\Attribute\AttributeCrud;
use App\CrudComponents\Author\AuthorCrud;
use App\CrudComponents\Citation\CitationCrud;
use App\CrudComponents\Conference\ConferenceCrud;
use App\CrudComponents\ConferenceYear\ConferenceYearCrud;
use App\CrudComponents\Group\GroupCrud;
use App\CrudComponents\Journal\JournalCrud;
use App\CrudComponents\PublicationTag\PublicationTagCrud;
use App\CrudComponents\Publisher\PublisherCrud;
use App\CrudComponents\Reference\ReferenceCrud;
use App\Forms\BaseForm;
use Nette;
use App\Model;
use App\Helpers;


class PublicationPresenter extends SecuredPresenter {

    /** @var array */
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

    /** @var array */
    public $publication;

    /** @var array */
    public $conferences;

    /** @var array */
    public $conferenceYears;

    public $journals;

    public $pdfParser;

    /** @var string */
    public $fulltext;

    public $functions;

    //--

    /** @var  Model\Publication  @inject */
    public $publicationModel;

    /** @var  Model\Publisher @inject */
    public $publisherModel;

    /** @var  Model\Journal @inject */
    public $journalModel;

    /** @var  Model\Conference @inject */
    public $conferenceModel;

    /** @var  Model\ConferenceYear @inject */
    public $conferenceYearModel;

    /** @var  Model\Attribute @inject */
    public $attributeModel;

    /** @var  Model\Author @inject */
    public $authorModel;

    /** @var  Model\Group @inject */
    public $groupModel;

    /** @var  Model\Files @inject */
    public $filesModel;

    /** @var Model\Annotation @inject */
    public $annotationModel;

    /** @var Model\Tag @inject */
    public $tagModel;


    /** @var Model\Reference @inject */
    public $referenceModel;

    /** @var Model\AttribStorage @inject */
    public $attribStorageModel;

    /** @var Model\Springer @inject */
    public $springerService;

    /** @var Model\GroupHasPublication @inject */
    public $groupHasPublicationModel;

    /** @var Model\CategoriesHasPublication @inject */
    public $categoriesHasPublicationModel;

    /** @var Model\AuthorHasPublication @inject */
    public $authorHasPublicationModel;

    /** @var Model\Documents @inject */
    public $documentsModel;

    /** @var Model\SubmitterHasPublication @inject */
    public $submitterHasPublicationModel;

    /** @var Model\PublicationIsbn @inject */
    public $publicationIsbnModel;

    //--

    /** @var  \App\Factories\IAnnotationCrudFactory @inject */
    public $annotationCrudFactory;

    /** @var  \App\Factories\IPublicationTagCrudFactory @inject */
    public $publicationTagCrudFactory;

    /** @var  \App\Factories\IPublisherCrudFactory @inject */
    public $publisherCrudFactory;

    /** @var  \App\Factories\IJournalCrudFactory @inject */
    public $journalCrudFactory;

    /** @var  \App\Factories\IConferenceYearCrudFactory @inject */
    public $conferenceYearCrudFactory;

    /** @var  \App\Factories\IConferenceCrudFactory @inject */
    public $conferenceCrudFactory;

    /** @var  \App\Factories\IAttributeCrudFactory @inject */
    public $attributeCrudFactory;

    /** @var  \App\Factories\IGroupCrudFactory @inject */
    public $groupCrudFactory;

    /** @var  \App\Factories\IAuthorCrudFactory @inject */
    public $authorCrudFactory;

    /** @var  \App\Factories\IReferenceCrudFactory @inject */
    public $referenceCrudFactory;

    /** @var  \App\Factories\ICitationCrudFactory @inject */
    public $citationCrudFactory;

    /** @var  \App\Factories\IPublicationCategoryListFactory @inject */
    public $publicationCategoryListFactory;

    /** @var \App\Forms\PublicationAddNewFormFactory @inject */
    public $publicationAddNewFormFactory;

    // --

    /** @var int */
    protected $publicationId;

    protected $selectedPublisherId;
    protected $selectedJournalId;

    /** @persistent */
    public $selectedConferenceId;

    /** @persistent */
    public $selectedConferenceYearId;

    protected $selectedGroupId;
    protected $selectedAuthorId;



    // --


    /**
     * PublicationPresenter constructor.
     */
    public function __construct()
    {
        $this->functions = new Helpers\Functions();
    }


    /**
     * @throws Nette\Application\AbortException
     */
    protected function startup(): void
    {
        parent::startup();
        $this->fulltext = '';

        $this->publication = [];
        $this->conferences = [];
        $this->conferencesYears = [];

        $this->drawAllowed = false;

        $this->types = [
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
        ];
    }


    /**
     *
     */
    public function beforeRender(): void
    {
        parent::beforeRender();
        $recordsStarredTemp = $this->submitterHasPublicationModel->findAllBy(['submitter_id' => $this->user->id]);
        $this->template->recordsStarred = [];

        foreach ($recordsStarredTemp as $record) {
            $this->template->recordsStarred[] = $record->publication_id;
        }
    }


    /**
     *
     */
    public function actionDefault(): void
    {

    }


    /**
     *
     */
    public function renderDefault(): void
    {

    }


    /**
     * @param string $name
     * @return \App\Components\AlphabetFilter\AlphabetFilterComponent
     * @throws \ReflectionException
     */
    public function createComponentAlphabetFilter(string $name): AlphabetFilterComponent
    {
        $c = new AlphabetFilterComponent($this, $name);

        $c->setAjaxRequest(false);
        $c->onFilter[] = function () {
            $this->resetPagination();
        };

        return $c;
    }


    /**
     * @param string $name
     * @return \PublicationImportForm
     */
    protected function createComponentPublicationImportForm(string $name): \PublicationImportForm
    {
        $form = new \PublicationImportForm($this, $name);

        $form->onSuccess[] = function(\PublicationImportForm $form) {
            $formValues = $form->getValues();

            $definitionTemplate = preg_replace('/\r+/', '<br />', $formValues['definition']);
            $definitionTemplate = preg_replace('/\n+/', '<br />', $definitionTemplate);

            if ($formValues['type'] == "bibtex") {
                $definition = preg_replace('/\r+/', ' ', $formValues['definition']);
                $definition = preg_replace('/\n+/', ' ', $definition);

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
                $authors = isset($fields['authors']) ? $fields['authors'] : [];
            } elseif ($formValues['type'] == "refworks") {
                $parser = new Helpers\EndNoteRefWorksParser($formValues['definition'], 'refworks');
                $parser->readLines();
                $fields = $parser->getFields();
                $pub_type = isset($fields['pub_type']) ? $fields['pub_type'] : 'misc';
                $authors = isset($fields['authors']) ? $fields['authors'] : [];
            }

            $selectedAuthors = [];

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
                    $journal_id2 = $this->journalModel->findOneBy(array(':journal_isbn.isbn' => $issn));

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
                $journalForm = $this['journalCrud']['journalForm']; /** @var $journalForm \App\CrudComponents\Journal\JournalForm */
                $journalForm['name']->setDefaultValue(isset($journal) ? $journal : '');
                if (!empty($issn)) {
                    $journalForm['isbn_count']->setValue(1);
                    $journalForm['isbn'][0]['isbn']->setDefaultValue(isset($issn) ? $issn : '');
                }
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
        };
        return $form;
    }


    /**
     * @param string $name
     * @return \App\CrudComponents\Reference\ReferenceCrud
     */
    protected function createComponentReferenceCrud(string $name): ReferenceCrud
    {
        $c = new ReferenceCrud($this->publicationId, $this->getUser(), $this->publicationModel, $this->referenceModel, $this, $name);
        //$c = $this->referenceCrudFactory->create($this->publicationId);

        if (!$this->publicationId) {
            $c->disallowAction('add');
        }

        $cbFn = function () {
            $references = $this->referenceModel->findAllBy(['publication_id' => $this->publication->id])->order("id ASC");

            $this->template->references = $references;

            $this->successFlashMessage('Operation has been completed successfully.');
            $this->redrawControl('publicationReferencesData');
        };

        $c->onAdd[] = $cbFn;
        $c->onDelete[] = $cbFn;
        $c->onEdit[] = $cbFn;

        return $c;
    }

    /**
     * @param string $name
     * @return \App\CrudComponents\Citation\CitationCrud
     */
    protected function createComponentCitationCrud(string $name): CitationCrud
    {
        $c = $this->citationCrudFactory->create($this->publicationId);
        if (!$this->publicationId) {
            $c->disallowAction('add');
        }

        $cbFn = function () {
            $this->successFlashMessage('Operation has been completed successfully.');
            $this->redrawControl('citationsShowAllRecords');
        };

        $c->onAdd[] = $cbFn;
        $c->onDelete[] = $cbFn;
        $c->onEdit[] = $cbFn;

        return $c;
    }


    /**
     * @return \App\CrudComponents\Annotation\AnnotationCrud
     */
    protected function createComponentAnnotationCrud(): AnnotationCrud
    {
        $c = $this->annotationCrudFactory->create($this->publicationId);

        if (!$this->publicationId) {
            $c->disallowAction('add');
        }

        $cbFn = function () {
            if ($this->user->isInRole('admin')) {
                $annotations = $this->annotationModel->findAllBy(['publication_id' => $this->publication->id])->order("id ASC");
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

    /**
     * @return \App\CrudComponents\PublicationTag\PublicationTagCrud
     */
    protected function createComponentPublicationTagCrud(): PublicationTagCrud
    {
        $c = $this->publicationTagCrudFactory->create($this->publicationId);

        if (!$this->publicationId) {
            $c->disallowAction('add');
        }

        $cbFn = function () {
            $this->successFlashMessage('Operation has been completed successfully.');
            $this->redrawControl('publicationTagData');
        };

        $c->onAdd[] = $cbFn;
        $c->onDelete[] = $cbFn;
        $c->onEdit[] = $cbFn;

        return $c;
    }


    /**
     * @param int $count
     */
    public function handleAddIsbn(int $count): void
    {
      $this['publicationAddNewForm']['isbn_count']->setValue($count);
      $this->redrawControl('isbn_count');

      $this->redrawControl("add_isbn");
      $this->redrawControl("last_isbn");
    }


    /**
     * @param int $id
     * @throws Nette\Application\BadRequestException
     */
    public function actionAddNew(?int $id): void
    {
        // rozlisovat ADD, EDIT (id), smazat vsechny kategorie a vytvorit nove!!, u EDIT skryt IMPORT
        $params = $this->getHttpRequest()->getQuery();
        $this->publicationId = $id;

        $this->publication = [];

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
        $this->conferenceYears = [];
        $this->authors = $this->authorModel->getAuthorsNames();
        $this->loadAttributes();
        $this->group = $this->groupModel->findAll()->order('name ASC');
        // ===========================================
        $selectedGroups = [];
        $selectedAuthors = [];
        $files = [];

        if ($id) {
            $selectedAuthors = $this->authorModel->getAuthorsNamesByPubId($id);
            $selectedConferenceYear = false;
            $this->attribStorage = $this->attribStorageModel->findAllBy(["publication_id" => $id]);

            $selectedGroups = $this->groupHasPublicationModel->findAllBy(['publication_id' => $id]);

            if (isset($this->publication['conference_year_id'])) {
                $selectedConferenceYear = $this->conferenceYearModel->find($this->publication['conference_year_id']);
                $this->selectedConferenceId = $selectedConferenceYear->conference_id;
                $this->selectedConferenceYearId = $selectedConferenceYear->id;
                $this->publication['conference'] = $selectedConferenceYear->conference_id;
                $this->loadConferenceYears();
            }

            $selectedCategories = $this->categoriesHasPublicationModel->findAllBy(['publication_id' => $id]);
            $selCatIds = [];
            foreach($selectedCategories as $selectedCategory) {
                $selCatIds[] = $selectedCategory->id;
            }
            $this['publicationAddNewForm']['categories']->setDefaultValue(implode(' ', $selCatIds));

        }
        // ===========================================
        $this->template->selectedAuthors = $selectedAuthors;
        $this->template->selectedGroups = $selectedGroups;
        if (!$id) {
            $this->template->files = $files;
        }
        $this->template->fileDeleted = false;
        $this->template->publication = $this->publication;
        $this->template->authors = $this->authors;
        $this->template->title = $title;
        $this->template->attributeInfo = false;
        $this->template->groupTree = $this->group;

        if ($id) {
            unset($this->publication['isbn']);
            $this['publicationAddNewForm']->setDefaults($this->publication);

            foreach ($this->attribStorage as $atSt) {
                $this['publicationAddNewForm']['attributes'][$atSt->attributes_id]->setDefaultValue($atSt->value);
            }

            if ($selectedConferenceYear) {
                $this->template->conferenceYearInfo = $selectedConferenceYear;
                if($selectedConferenceYear->publisher_id)
                    $this->template->conferenceYearPublisherInfo = $selectedConferenceYear->ref('publisher');
            }

            if (isset($this->publication['publisher_id']) && $this->publication['pub_type'] != 'inproceedings' && $this->publication['pub_type'] != 'proceedings') {
                $this->template->publisherInfo_alone = $this->publisherModel->find($this->publication['publisher_id']);
                $this->selectedPublisherId = $this->publication['publisher_id'];
            }

            if (isset($this->publication['journal_id'])) {
                $this->selectedJournalId = $this->publication['journal_id'];
                $this->template->journalInfo = $this->journalModel->find($this->publication['journal_id']);
            }

            if (isset($this->publication['conference'])) {
                $this->template->conferenceInfo = $this->conferenceModel->find($this->publication['conference']);
            }
        }

        $this->template->getLatte()->addProvider('formsStack', [$this['publicationAddNewForm']]);
        $this->template->id = $id;
    }


    /**
     *
     */
    public function renderAddNew(): void
    {
        $this->template->isbn_count = $this['publicationAddNewForm']['isbn_count']->getValue();
        $count = $this['publicationAddNewForm']['isbn_count']->getValue() + 1;
        $cont = $this['publicationAddNewForm']['isbn'];

        for ($i = 0; $i < $count; $i++) {
            $cont2 = $cont->addContainer($i);
            $cont2->addText("isbn", "ISBN/ISSN")->setRequired(false);
            $cont2->addSelect("type", "Typ", ["ISBN" => "ISBN", "ISSN" => "ISSN"]);
            $cont2->addText("note", "Poznámka")->setRequired(false);
        }

        if ($this->publicationId) {
            $isbn = $this->publicationIsbnModel->findAllBy(["publication_id" => $this->publicationId]);
            $i = 0;
            foreach ($isbn as $row) {
                $cont[$i]['isbn']->setDefaultValue($row['isbn']);
                $cont[$i]['type']->setDefaultValue($row['type']);
                $cont[$i]['note']->setDefaultValue($row['note']);
                $i++;
            }
        }

        if (!isset($this->template->files) && $this->publicationId) {
            $this->template->files = $this->filesModel->prepareFiles($this->publicationId);
        }

        $this->template->selectedPublisherId = $this->selectedPublisherId;
        $this->template->publisherInfo = !$this->selectedPublisherId ? null : $this->publisherModel->find($this->selectedPublisherId);

        $this->template->selectedJournalId = $this->selectedJournalId;
        $this->template->journalInfo = !$this->selectedJournalId ? null : $this->journalModel->find($this->selectedJournalId);

        $this->template->selectedConferenceId = $this->selectedConferenceId;
        $this->template->conferenceInfo = !$this->selectedConferenceId ? null : $this->conferenceModel->find($this->selectedConferenceId);

        $this->template->selectedConferenceYearId = $this->selectedConferenceYearId;
        $this->template->conferenceYearInfo = !$this->selectedConferenceYearId ? null : $this->conferenceYearModel->find($this->selectedConferenceYearId);

        $this->template->selectedGroupId = !$this->selectedGroupId ? null : $this->selectedGroupId;

        $this->template->selectedAuthorId = !$this->selectedAuthorId ? null : $this->selectedAuthorId;

        $this->template->attributes = $this->attributes;

        if (!isset($this->template->authorDeleted)) {
            $this->template->authorDeleted = false;
        }

        if (!isset($this->template->groupDeleted)) {
            $this->template->groupDeleted = false;
        }
        if (!isset($this->template->groupEdited)) {
            $this->template->groupEdited = false;
        }
        if (!isset($this->template->groupAdded)) {
            $this->template->groupAdded = false;
        }

        if (!isset($this->template->authorDeleted)) {
            $this->template->authorDeleted = false;
        }
        if (!isset($this->template->authorEdited)) {
            $this->template->authorEdited = false;
        }
        if (!isset($this->template->authorAdded)) {
            $this->template->authorAdded = false;
        }
    }


    /**
     * @return \App\CrudComponents\ConferenceYear\ConferenceYearCrud
     */
    protected function createComponentConferenceYearCrud(): ConferenceYearCrud
    {
        $c = $this->conferenceYearCrudFactory->create($this->selectedConferenceId ? $this->selectedConferenceId : 0);

        $c->onAdd[] = function ($row) {
            $this->loadConferenceYears(true);
            $this->redrawControl('publicationAddNewForm-conference_year_id');
            $this['publicationAddNewForm']['conference_year_id']->setValue($row->id);
            $this->handleShowConferenceYearInfo($row->id);
            $this->redrawControl('conferenceYearInfo');
        };

        $c->onEdit[] = function ($row) {
            $this->loadConferenceYears(true);
            $this->redrawControl('publicationAddNewForm-conference_year_id');
            $this->selectedConferenceId = $row->conference_id;
            $this->loadConferenceYears(true);
            $this['publicationAddNewForm']['conference_year_id']->value = $row->id;
            $this->handleShowConferenceYearInfo($row->id);
            $this->redrawControl('conferenceYearInfo');
        };

        $c->onDelete[] = function ($row) {
            $this->loadConferenceYears(true);
            $this->redrawControl('publicationAddNewForm-conference_year_id');
            $this['publicationAddNewForm']['conference_year_id']->setValue(null);
            $this->handleShowConferenceYearInfo(0);
            $this->redrawControl('conferenceYearInfo');
        };

        return $c;
    }

    /**
     * @param null $parent
     * @param null $name
     * @return \App\Forms\BaseForm
     */
    public function createComponentPublicationAddNewForm($parent = null, $name = null): BaseForm
    {
        if (!$this->journals) {
            $this->loadJournals();
        }
        if (!$this->publishers) {
            $this->loadPublishers();
        }
        if (!$this->conferences) {
            $this->loadConferences();
        }
        if (!$this->conferenceYears) {
            $this->loadConferenceYears();
        }
        if (!$this->attributes) {
            $this->loadAttributes();
        }

        $onFormSuccess = function ($form) {
            $this->publicationModel->beginTransaction();
            $documentsObjectCreatedId = null;

            try {
                $data = $form->getValuesTransformed();
                $formValues = $form->getValuesTransformed();
                $values = $form->getHttpData();
                $formValues['conference_year_id'] = $values['conference_year_id'];

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
                unset($formValues['attributes']);
                unset($formValues['isbn']);
                unset($formValues['isbn_count']);

                if ($form->values->id) {
                    $formValues['id'] = $form->values->id;
                    $record = $this->publicationModel->update($formValues);
                    $record = $this->publicationModel->find($form->values->id);
                } else {
                    $record = $this->publicationModel->insert($formValues);
                }

                if ($form->values->id) {
                    $authorHasPublication = $this->authorHasPublicationModel->findAllBy(["publication_id" => $form->values->id]);

                    foreach ($authorHasPublication as $item) {
                        $item->delete();
                    }

                    $categoriesHasPublication = $this->categoriesHasPublicationModel->findAllBy(["publication_id" => $form->values->id]);
                    foreach ($categoriesHasPublication as $item) {
                        $item->delete();
                    }

                    $groupHasPublication = $this->groupHasPublicationModel->findAllBy(["publication_id" => $form->values->id]);
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
                    $newDirPath = $this->filesModel->dirPath . $record->id;
                    $newFilePath = $newDirPath . "/" . rand(10, 99) . "_" . $file->getName();

                    $file->move($newFilePath);
                    //  $extension = $this->getFileExtension($file->getName());
                    $extension = $this->filesModel->getFileExtension($file->getName());

                    switch ($extension) {
                        case "pdf":
                            $this->pdfParser = new \Smalot\PdfParser\Parser();
                            $extractedText = $this->pdfExtractorFirstPage($newFilePath) ?? "";
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

                $authors = [];
                $categories = [];
                $group = [];

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
                    $this->categoriesHasPublicationModel->insert([
                        'categories_id' => $value,
                        'publication_id' => $record->id
                    ]);
                }

                foreach ($group as $key => $value) {
                    $this->groupHasPublicationModel->insert([
                        'group_id' => $value,
                        'publication_id' => $record->id
                    ]);
                }


                if ($form->values->pub_type != "proceedings") {
                    foreach ($authors as $key => $value) {
                        $this->authorHasPublicationModel->insert([
                            'author_id' => $value,
                            'publication_id' => $record->id,
                            'priority' => $key
                        ]);
                    }
                }

                // ATTRIBUTES

                if ($form->values->id) {
                    $attribStorage = $this->attribStorageModel->findAllBy(["publication_id" => $form->values->id]);
                    foreach ($attribStorage as $item) {
                        $item->delete();
                    }
                }

                foreach ($this->attributes as $attrib) {
                    if (!empty($values['attributes'][$attrib->id])) {
                        $this->attribStorageModel->insert([
                            'publication_id' => $record->id,
                            'attributes_id' => $attrib->id,
                            'submitter_id' => $this->user->id,
                            'value' => $values['attributes'][$attrib->id],
                        ]);
                    }
                }

                $this->publicationIsbnModel->findAllBy(["publication_id" => $record->id])
                    ->delete();

                if (!empty($values['isbn'])) {
                    foreach ($values['isbn'] as $isbn) {
                        if (empty($isbn['isbn']) && empty($isbn['note']) ) {
                            continue;
                        }
                        $this->publicationIsbnModel->insert(["publication_id" => $record->id,
                            "isbn" => $isbn['isbn'],
                            "type" => $isbn['type'],
                            "note" => $isbn['note']]);
                    }
                }

                $this->publicationModel->commitTransaction();
                $this->flashMessage('Operation has been completed successfullly.', 'alert-success');

            } catch (\Exception $e) {
                $this->publicationModel->rollbackTransaction();
                if($documentsObjectCreatedId) {
                    $this->documentsModel->delete($documentsObjectCreatedId);
                }
                throw $e;
            }

            if (!$this->isAjax()) {
                $this->presenter->redirect('Publication:showpub', $record->id);
            } else {
                $this->redrawControl();
            }
        };


        $form = $this->publicationAddNewFormFactory->create($this->publicationId, $this->selectedConferenceId, $this->types, $this, $onFormSuccess);

        return $form;
    }


    /**
     * @param string $newFilePath
     * @return mixed
     */
    public function pdfExtractor(string $newFilePath)
    {
        $pdf = $this->pdfParser->parseFile($newFilePath);
        $text = $pdf->getText();
        return $text;
    }


    /**
     * @param string $newFilePath
     * @return mixed
     */
    public function pdfExtractorFirstPage(string $newFilePath)
    {
        try {
            $pdf = $this->pdfParser->parseFile($newFilePath);
            $pages = $pdf->getPages();
            return $pages[0]->getText();
        } catch (\Exception $e) {
            return null;
        }
    }


    /**
     * @param int|null $fileId
     * @throws Nette\Application\AbortException
     */
    public function handleDeleteFile(?int $fileId): void
    {
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


    /**
     * @param $sort
     * @param $order
     * @param $keywords
     * @param $filter
     */
    public function actionShowAll($sort, $order, $keywords, $filter): void
    {
        $this->drawAllowed = true;
        $this->template->publicationDeleted = false;
    }


    /**
     *
     */
    public function renderShowAll(): void
    {
        if ($this->drawAllowed) {
            $starred = $this['individualFilter']->getActiveButtonName() == 'starred';
            $my = $this['individualFilter']->getActiveButtonName() == 'my';
            $this->drawPublications($starred, $my);
        }
    }


    /**
     * @param bool $starred
     * @param bool $my
     */
    public function drawPublications(bool $starred = false, bool $my = false): void
    {
        /** @var $alphabetFilter \App\Components\AlphabetFilter\AlphabetFilterComponent */
        $alphabetFilter = $this["alphabetFilter"];
        $params = $this->getHttpRequest()->getQuery();

        if (!isset($params['sort'])) {
            $params['sort'] = 'title';
        }

        if (!isset($params['order'])) {
            $params['order'] = 'ASC';
        }

  			if($alphabetFilter->getFilter()) {
            $params['filter'] = $alphabetFilter->getFilter();
        } else {
          $params['filter'] = 'none';
        }

        if (!isset($this->template->records)) {
            $this->records = $this->publicationModel->findAllByKw($params);

            if($starred) {
                $this->records->where(':submitter_has_publication.submitter_id = ?', $this->user->id);
            }

            if($my) {
                $this->records->where(':author_has_publication.author.user_id = ?', $this->user->id);
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
        }

        $authorsByPubId = [];

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


    /**
     * @param string $name
     * @return \PublicationShowAllSearchForm
     */
    protected function createComponentPublicationShowAllSearchForm(string $name): \PublicationShowAllSearchForm
    {
        $form = new \PublicationShowAllSearchForm($this, $name);
        return $form;
    }


    /**
     * @param int|null $id
     * @throws Nette\Application\BadRequestException
     */
    public function actionShowPub(?int $id): void
    {
        $this->publication = $this->publicationModel->find($id);

        if (!$this->publication) {
            $this->error('Publication not found');
        }

        $this->publicationId = $id;
    }


    /**
     *
     */
    public function renderShowPub(): void
    {
        $data = $this->publicationModel->getAllPubInfo($this->publication);
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
        $this->template->submitter = $data['submitter'];
        $authorsByPubId = [];

        foreach ($this->template->references as $rec) {
            if (empty($rec->reference_id)) {
                continue;
            }
            /** @var $rec Nette\Database\Table\ActiveRow */
            foreach($rec->reference->related('author_has_publication')->order('priority ASC') as $authHasPub) {
                $author = $authHasPub->ref('author');
                if(!isset($authorsByPubId[$rec->reference->id])) $authorsByPubId[$rec->reference->id] = [];
                $authorsByPubId[$rec->reference->id][] = $author;
            }
        }

        foreach ($this->template->citations as $rec) {
            /** @var $rec Nette\Database\Table\ActiveRow */
            foreach($rec->publication->related('author_has_publication')->order('priority ASC') as $authHasPub) {
                $author = $authHasPub->ref('author');
                if(!isset($authorsByPubId[$rec->publication->id])) $authorsByPubId[$rec->publication->id] = [];
                $authorsByPubId[$rec->publication->id][] = $author;
            }
        }

        $this->template->authorsByPubId = $authorsByPubId;
        $_this = $this;
        $this->template->getLatte()->addFilter('template', function($text) use ($_this) {
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

        if ($this->user->isInRole('admin')) {
            $tags = $this->tagModel->findAllBy([':publication_has_tag.publication_id' => $this->publication->id])->order("id ASC");
        } else {
            $tags = $this->tagModel->findAllForReaderOrSubmitter($this->publication->id, $this->user->id);
        }

        $this->template->tags = $tags;
    }

    // ====================================================================
    // ==================== P U B L I C A T I O N =========================
    // ====================================================================


    /**
     * @param int|null $publicationId
     * @throws Nette\Application\AbortException
     * @throws Nette\Application\BadRequestException
     */
    public function handleDeletePublication(?int $publicationId): void
    {
        $this->drawAllowed = true;
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


    /**
     * @param int $referenceId
     * @throws Nette\Application\AbortException
     * @throws Nette\Application\BadRequestException
     */
    public function handleDeleteReference(int $referenceId): void
    {
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


    /**
     * @param int $favorite_id
     * @throws Nette\Application\AbortException
     */
    public function handleSetFavouritePub(int $favorite_id): void
    {
        $this->submitterHasPublicationModel->insert([
            'publication_id' => $favorite_id,
            'submitter_id' => $this->user->id
        ]);

        $this->flashMessage('Publication has been added to starred.', 'alert-success');

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl('flashMessages');
            $this->redrawControl('publicationAdminButtons');
        }
    }


    /**
     * @param int $favorite_id
     * @throws Nette\Application\AbortException
     */
    public function handleUnsetFavouritePub(int $favorite_id): void
    {
        $record = $this->submitterHasPublicationModel->findOneBy([
            'publication_id' => $favorite_id,
            'submitter_id' => $this->user->id
        ]);

        if ($record) {
            $record->delete();
        }

        $this->flashMessage('Publication has been removed from starred.', 'alert-success');

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl('flashMessages');
            $this->redrawControl('publicationAdminButtons');
        }
    }

    // new


    // --

    /**
     * @return \App\Components\PublicationCategoryList\PublicationCategoryListComponent
     */
    protected function createComponentPublicationCategoryList(): PublicationCategoryListComponent
    {
        $c = $this->publicationCategoryListFactory->create();

        $c->setIsSelectable(true);
        $c->setHasThreeStates(false);
        $c->setHasControls(true);

        return $c;
    }


    /**
     * @return \App\CrudComponents\Publisher\PublisherCrud
     */
    public function createComponentPublisherCrud(): PublisherCrud
    {
        $publisherCrud  = $this->publisherCrudFactory->create();

        $publisherCrud->onAdd[] = function ($record) {
            $this->handleShowPublisherInfo($record->id);
            $this->loadPublishers(true);
            $this["publicationAddNewForm"]["publisher_id"]->setValue($record->id);
            $this->selectedPublisherId = $record->id;
            $this->redrawControl('publicationAddNewForm-publisher_id');
            $this->redrawControl('publisherControls');
            $this->redrawControl('publisherInfo');
        };

        $publisherCrud->onDelete[] = function ($record) {
            $this->handleShowPublisherInfo($record->id);
            $this->loadPublishers(true);
            $this["publicationAddNewForm"]["publisher_id"]->setValue(null);
            $this->selectedPublisherId = null;
            $this->redrawControl('publicationAddNewForm-publisher_id');
            $this->redrawControl('publisherControls');
            $this->redrawControl('publisherInfo');
        };

        $publisherCrud->onEdit[] = function ($record)  {
            $this->handleShowPublisherInfo($record->id);
            $this->loadPublishers(true);
            $this["publicationAddNewForm"]["publisher_id"]->setValue($record->id);
            $this->redrawControl('publicationAddNewForm-publisher_id');
            $this->redrawControl('publisherInfo');
        };

        return $publisherCrud;
    }


    /**
     * @return \App\CrudComponents\Journal\JournalCrud
     */
    public function createComponentJournalCrud(): JournalCrud
    {
        $journalCrud  = $this->journalCrudFactory->create();

        $journalCrud->onAdd[] = function ($record) {
            $this->handleShowJournalInfo($record->id);
            $this->loadJournals(true);
            $this["publicationAddNewForm"]["journal_id"]->setValue($record->id);
            $this->selectedJournalId = $record->id;
            $this->redrawControl('publicationAddNewForm-journal_id');
            $this->redrawControl('journalControls');
            $this->redrawControl('journalInfo');
        };

        $journalCrud->onDelete[] = function ($record) {
            $this->handleShowJournalInfo($record->id);
            $this->loadJournals(true);
            $this["publicationAddNewForm"]["journal_id"]->setValue(null);
            $this->selectedJournalId = null;
            $this->redrawControl('publicationAddNewForm-journal_id');
            $this->redrawControl('journalControls');
            $this->redrawControl('journalInfo');
        };

        $journalCrud->onEdit[] = function ($record)  {
            $this->handleShowJournalInfo($record->id);
            $this->loadJournals(true);
            $this["publicationAddNewForm"]["journal_id"]->setValue($record->id);
            $this->redrawControl('publicationAddNewForm-journal_id');
            $this->redrawControl('journalInfo');
        };

        return $journalCrud;
    }


    /**
     * @return \App\CrudComponents\Conference\ConferenceCrud
     */
    public function createComponentConferenceCrud(): ConferenceCrud
    {
        $conferenceCrud  = $this->conferenceCrudFactory->create();

        $conferenceCrud->onAdd[] = function ($record) {
            $this->handleShowConferenceInfo($record->id);
            $this->loadConferences(true);
            $this["publicationAddNewForm"]["conference"]->setValue($record->id);
            $this->selectedConferenceId = $record->id;
            $this->redrawControl('publicationAddNewForm-conference');
            $this->redrawControl('publicationAddNewForm-conference_year_id');
            $this->redrawControl('conferenceControls');
            $this->redrawControl('conferenceInfo');
        };

        $conferenceCrud->onDelete[] = function ($record) {
            $this->handleShowConferenceInfo($record->id);
            $this->loadConferences(true);
            $this["publicationAddNewForm"]["conference"]->setValue(null);
            $this->selectedConferenceId = null;
            $this->redrawControl('publicationAddNewForm-conference');
            $this->redrawControl('publicationAddNewForm-conference_year_id');
            $this->redrawControl('conferenceControls');
            $this->redrawControl('conferenceInfo');
        };

        $conferenceCrud->onEdit[] = function ($record)  {
            $this->handleShowConferenceInfo($record->id);
            $this->loadConferences(true);
            $this["publicationAddNewForm"]["conference"]->setValue($record->id);
            $this->redrawControl('publicationAddNewForm-conference');
            $this->redrawControl('conferenceInfo');
        };

        $conferenceCrud->onCreateConferenceYearCrud[] = function (ConferenceYearCrud &$c) {
            $fnRedraw = function () use ($c) {
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


    /**
     * @return \App\CrudComponents\Attribute\AttributeCrud
     */
    public function createComponentAttributeCrud(): AttributeCrud
    {
        $attributeCrud  = $this->attributeCrudFactory->create();

        $attributeCrud->onAdd[] = function ($record) {
            $this->loadAttributes(true);
            $this->redrawControl('attributeShowAllRecords');
        };

        $attributeCrud->onDelete[] = function ($record) {
            $this->loadAttributes(true);
            $this->redrawControl('attributeShowAllRecords');
        };

        $attributeCrud->onEdit[] = function ($record) {
            $this->loadAttributes(true);
            $this->redrawControl('attributeShowAllRecords');
        };

        return $attributeCrud;
    }


    /**
     * @return \App\CrudComponents\Group\GroupCrud
     */
    public function createComponentGroupCrud(): GroupCrud
    {
        $c = $this->groupCrudFactory->create();

        $c->onAdd[] = function ($record) {
            $this->template->groupAdded = $record;
            $this->redrawControl('groupAdded');
        };

        $c->onEdit[] = function ($record) {
            $this->template->groupEdited = $record;
            $this->redrawControl('groupEdited');
        };

        $c->onDelete[] = function ($record) {
            $this->template->groupDeleted = $record;
            $this->redrawControl('groupDeleted');
        };

        return $c;
    }


    /**
     * @return \App\CrudComponents\Author\AuthorCrud
     */
    public function createComponentAuthorCrud(): AuthorCrud
    {
        $c = $this->authorCrudFactory->create();

        $c->onAdd[] = function ($record) {
            $this->template->authorAdded = $record;
            $this->redrawControl('authorAdded');
        };

        $c->onEdit[] = function ($record) {
            $this->template->authorEdited = $record;
            $this->redrawControl('authorEdited');
        };

        $c->onDelete[] = function ($record) {
            $this->template->authorDeleted = $record;
            $this->redrawControl('authorDeleted');
        };

        return $c;
    }


    // =======================   DATA LOADERS    =======================

    /**
     * @param bool $updateDependencies
     */
    protected function loadPublishers(bool $updateDependencies = false): void
    {
        $this->publishers =  $this->publisherModel->findAll()->order("name ASC")->fetchPairs('id', 'name');

        if ($updateDependencies) {
            if (isset($this['publicationAddNewForm'])) {
                $this['publicationAddNewForm']['publisher_id']->setItems($this->publishers);
            }
        }
    }


    /**
     * @param bool $updateDependencies
     */
    protected function loadJournals(bool $updateDependencies = false): void
    {
        $this->journals =  $this->journalModel->findAll()->order("name ASC")->fetchPairs('id', 'name');

        if ($updateDependencies) {
            if (isset($this['publicationAddNewForm'])) {
                $this['publicationAddNewForm']['journal_id']->setItems($this->journals);
            }
        }
    }


    /**
     * @param bool $updateDependencies
     */
    protected function loadConferences(bool $updateDependencies = false): void
    {
        $this->conferences = $this->conferenceModel->getConferenceForSelectbox();

        if ($updateDependencies) {
            if (isset($this['publicationAddNewForm'])) {
                $this['publicationAddNewForm']['conference']->setItems($this->conferences);
            }
        }
    }


    /**
     * @param bool $updateDependencies
     */
    protected function loadConferenceYears(bool $updateDependencies = false): void
    {
        if ($this->selectedConferenceId) {
            $this->conferenceYears = $this->conferenceYearModel->getConferenceYearForSelectbox($this->selectedConferenceId);
            if ($updateDependencies) {
                if (isset($this['publicationAddNewForm'])) {
                    $this['publicationAddNewForm']['conference_year_id']->setItems($this->conferenceYears);
                }
            }
        } else {
            $this->conferenceYears = [];
        }
    }


    /**
     * @param bool $updateDependencies
     */
    protected function loadAttributes(bool $updateDependencies = false): void
    {
        $this->attributes =  $this->attributeModel->findAll()->order("name ASC");

        if($updateDependencies) {
            $cont = $this['publicationAddNewForm']['attributes'];
            foreach ($this->attributes as $attribute) {
                $label = $attribute->name . ' (' . $attribute->description . ')';
                if (empty($cont[$attribute->id])) {
                    $cont->addText($attribute->id, $label);
                } else {
                    $cont[$attribute->id]->caption = $label;
                }
            }
        }
    }



    // ==============================       SIGNALS        ===============================

    /**
     * @param int|null $publisherId
     * @throws Nette\Application\AbortException
     */
    public function handleShowPublisherInfo(?int $publisherId): void
    {
        $this->drawAllowed = false;
        $this->selectedPublisherId = $publisherId;

        if (!$this->isAjax()) {
            $this->redirect('this');
        } else {
            $this->redrawControl('publisherInfo');
            $this->redrawControl('publisherControls');
        }
    }


    /**
     * @param int|null $journalId
     * @throws Nette\Application\AbortException
     */
    public function handleShowJournalInfo(?int $journalId): void
    {
        $this->drawAllowed = false;
        $this->selectedJournalId = $journalId;

        if (!$this->isAjax()) {
            $this->redirect('this');
        } else {
            $this->redrawControl('journalInfo');
            $this->redrawControl('journalControls');
        }
    }


    /**
     * @param int|null $conferenceId
     * @throws Nette\Application\AbortException
     */
    public function handleShowConferenceInfo(?int $conferenceId): void
    {
        $this->drawAllowed = true;
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


    /**
     * @param int|null $conferenceYearId
     * @throws Nette\Application\AbortException
     */
    public function handleShowConferenceYearInfo(?int $conferenceYearId): void
    {
        $this->drawAllowed = false;
        $this->selectedConferenceYearId = $conferenceYearId;
        $conferenceYearInfo =  $this->conferenceYearModel->find($conferenceYearId);

        if (!empty($conferenceYearInfo['w_year'])) {
            $this['publicationAddNewForm']['issue_year']->setValue($conferenceYearInfo['w_year']);
        }

        if (!empty($conferenceYearInfo['w_from'])) {
            $this['publicationAddNewForm']['issue_month']->setValue($conferenceYearInfo['w_from']->format("n"));
        }

        if (!$this->isAjax()) {
            $this->redirect('this');
        } else {
            $this->redrawControl('conferenceYearInfo');
            $this->redrawControl('conferenceYearControls');
            $this->redrawControl('issueDate');
        }
    }


    /**
     * @param int|null $groupId
     * @throws Nette\Application\AbortException
     */
    public function handleSelectGroup(?int $groupId): void
    {
        $this->drawAllowed = false;
        $this->selectedGroupId = $groupId;

        if (!$this->isAjax()) {
            $this->redirect('this');
        } else {
            $this->redrawControl('groupControls');
        }
    }


    /**
     * @param int|null $authorId
     * @throws Nette\Application\AbortException
     */
    public function handleSelectAuthor(?int $authorId): void
    {
        $this->drawAllowed = false;
        $this->selectedAuthorId = $authorId;

        if (!$this->isAjax()) {
            $this->redirect('this');
        } else {
            $this->redrawControl('authorControls');
        }
    }

    // moved from basepresenter (what a mess!)

    /**
     * @param int $id
     * @throws Nette\Application\AbortException
     */
    public function handleSetConfirmed(int $id): void
    {
        $this->publicationModel->update(['id' => $id, 'confirmed' => 1]);
        $this->flashMessage('Operation has been completed successfully.', 'alert-success');

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl();
        }
    }


    /**
     * @param int $id
     * @throws Nette\Application\AbortException
     */
    public function handleSetUnConfirmed(int $id): void
    {
        $this->publicationModel->update(['id' => $id, 'confirmed' => 0]);
        $this->flashMessage('Operation has been completed successfully.', 'alert-success');

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl();
        }
    }


    /**
     * @param string $name
     * @return \PublicationSpringerForm
     */
    protected function createComponentPublicationSpringerForm(string $name): \PublicationSpringerForm
    {
        $form = new \PublicationSpringerForm($this, $name);
        $form->onSuccess[] = function (\PublicationSpringerForm $form) {
            $values = $form->getHttpData();

            if (!array_key_exists("data_springer", $values)) {
                $this->presenter->redirect('this');
            }

            $data = $this->springerService->fetchData($values['id_springer'], $values['type_springer'], false);
            $dataArr = $data['array'];
            $data = $data['object'];

            if ($data) {
                $this->publication = $this->springerService->parseData($data[$values['data_springer']]);
                $result = [];

                foreach ($this->publication['creators'] as $author) {
                    $parser = new Helpers\HumanNameParser_Parser($bodytag = str_replace(".", ". ", $author->creator));
                    $result[] = [
                        'name' => $parser->getFirst(),
                        'middlename' => $parser->getMiddle(),
                        'surname' => $parser->getLast()
                    ];
                }

                $selectedAuthors = [];

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
        };

        return $form;
    }


    /**
     * @param $idSpringer
     * @param $typeSpringer
     * @throws Nette\Application\AbortException
     */
    public function handleFetchFromSpringer($idSpringer, $typeSpringer): void
    {
        $radioArray = $this->springerService->fetchData($idSpringer, $typeSpringer);

        if ($radioArray && count($radioArray)) {
            $this["publicationSpringerForm"]["data_springer"]->setItems($radioArray); // set up new values
            $this->flashMessage('Operation has been completed successfully.', 'alert-success');
            $this->template->springerMessage = ['status' => 'success', 'message' => 'Data fetched successfully, please select specific data for importing from RadioBox above!'];
        } else {
            $this["publicationSpringerForm"]["data_springer"]->setItems([]); // set up new values
            $this->template->springerMessage = ['status' => 'danger', 'message' => 'No data found, please try again with different input!'];
        }


        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl('publicationSpringerForm-data');
        }
    }


    /**
     * @return \App\Components\ButtonToggle\ButtonGroupComponent
     */
    public function createComponentIndividualFilter(): ButtonGroupComponent
    {
        $c = new ButtonGroupComponent([
            'all'     =>  [
                'caption'   =>  'All publications',
                'icon'      =>  'list'
            ],
            'starred'      =>  [
                'caption'   =>  'Starred publications',
                'icon'      =>  'star'
            ],
            'my'            => [
                'caption'   => 'My publications',
                'icon'      => 'user'
            ]
        ], 'all');

        $c->onActiveButtonChanged[] = function () {
            $this->resetPagination();
        };

        return $c;
    }

}
