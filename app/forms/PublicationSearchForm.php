<?php

namespace App\Forms;

use \Nette\Application\UI;


class PublicationSearchForm {

    use \Nette\SmartObject;

    /** @var \Nette\Database\Connection */
    private $database;

    /** @var \App\Model\Tag */
    private $tagModel;

    /**
     * HomepageSearchForm constructor.
     * @param \Nette\Database\Connection $database
     * @param \App\Model\Tag $tagModel
     */
    public function __construct(\Nette\Database\Connection $database, \App\Model\Tag $tagModel)
    {
        $this->database = $database;
        $this->tagModel = $tagModel;
    }

    /**
     * @param array $data
     * @param int $userId
     * @return UI\Form
     */
    public function create(array $data, int $userId): UI\Form
    {
        $form = new UI\Form;

        $form->addText('keywords', 'Keywords')
          ->setRequired(false)
          ->addRule($form::MAX_LENGTH, 'Keywords is way too long', 100);

        $form->addText('author', 'Author')
            ->setRequired(false)
            ->addRule($form::MAX_LENGTH, 'Author is way too long', 100);

        $form->addRadioList('catop', 'Category operator', ['or' => 'OR', 'and' => 'AND'])
            ->setDefaultValue('or');

        $form->addText('categories', 'Publication categories');

        $form->addRadioList('stype', 'Search type', ['fulltext' => 'Fulltext', 'title' => 'Title only'])
            ->setDefaultValue('fulltext');

        $form->addRadioList('scope', 'Search scope', [
                'all' => 'All publications',
                'starred' => 'Starred by me',
                'annotated' => 'Annotated by me',
                'my' => 'My publications'
            ])->setDefaultValue('all');

        $form->addCheckboxList('pubtype', 'Publication type', [
            'misc' => 'Misc',
            'book' => 'Book',
            'article' => 'Article',
            'inproceedings' => 'InProceedings',
            'proceedings' => 'Proceedings',
            'incollection' => 'InCollection',
            'inbook' => 'InBook',
            'booklet' => 'Booklet',
            'manual' => 'Manual',
            'techreport' => 'Techreport',
            'mastersthesis' => 'Mastersthesis',
            'phdthesis' => 'Phdthesis',
            'unpublished' => 'Unpublished'
        ]);

        $tags = $this->tagModel->getTagsForSearchForm($userId);

        $form->addCheckboxList('tags', 'Publication tags', $tags);

        $form->addSubmit('send', 'Search');

        //deserialize pubtypes from url
        if (isset($data['ptype'])) {
            $pubtypes = explode(' ', $data['ptype']);
            $data['pubtype'] = $pubtypes;
        }

        //deserialize tags from url
        if (isset($data['tags'])) {
            $data['tags'] = explode(' ', $data['tags']);
        }

        $form->setDefaults($data);

        return $form;
    }

}
