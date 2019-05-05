<?php

namespace App\Model;

use Nette;

class Help {

    use Nette\SmartObject;

    /** @var array */
    public $arrayHelp;

    /**
     * Help constructor.
     */
    public function __construct()
    {
        $this->arrayHelp = [
            'publicationAddNewForm' => [
                'title' => 'Add new publication',
                'content' => 
                    'If you want to EDIT some field value, click on the EDIT button. The form for editing data will be displayed. ' 
                    .'If you want to DELETE some field value, click the DELETE button. '
                    .'To display a list of all publications of field value, click the ASSOCIATED PUBLICATIONS button. '
                    .'If you want to ADD NEW field value, click the ADD NEW button. The form for adding new data will be displayed.'
            ],

            'pub_type' => [
                'title' => 'Publication type',
                'content' =>
                    'Select type of your publication and then fill all the required fields (marked by red star).'
                    .'Available and required field are different for every publication type.'
            ],

            'authors' => [
                'title' => 'Authors',
                'content' =>
                    'There is a list of all authors in the All authors box. '
                    .'Select authors using drag & drop or click on them and click right arrow to move them to selected authors. '
                    .'Order of authors matters. Author with the highest priority is on top. '
                    .'Sort authors using drag & drop or click on one of them and move him with up and down arrows. '
                    .'You can also add new author by clicking + button or edit or delete existing when selected from either box.'
            ],

            'categories' => [
                'title' => 'Categories',
                'content' =>
                    'Select desired categories by checking box next to them. '
                    .'Show subcategories using an arrow next to the checkbox, if available. '
                    .'Select a category by clicking on its name and add subcategory, edit or delete it using buttons next to the box. '
            ],

            'upload' => [
                'title' => 'Upload',
                'content' =>
                    'Use file prompt to choose file to upload for this publication.'
            ],

            'attributes' => [
                'title' => 'Attributes',
                'content' =>
                    'Use attributes to extend set of publication attributes. '
                    .'Use existing attributes below or add new one using + button. '
                    .'Attribute can be private or global. '
                    .'The first character must be underline, other characters may be letters and numbers.'
            ],

            'groups' => [
                'title' => 'Groups',
                'content' =>
                    'There is a list of all user groups in the GROUPS box. '
                    .'If you want this publication to be part of some groups, please check them. '
                    .'If you want to do some action (EDIT, DELETE, ASSOCIATED PUBLICATIONS) with them, just mark them and then press one of the action buttons.'
            ],

            'springer' => [
                'title' => 'Springer',
                'content' =>
                    'If you want to fetch some data from Springer, please fill in the form correctly, then click FETCH DATA button. '
                    .'In the next step, select the data for import and click IMPORT DATA button.'
            ],

            'upcomingConferences' => [
                'title' => 'Upcoming conferences',
                'content' =>
                    'Conference is upcoming, if its notification date is less than your deadline notification advance days from today. '
                    .'Deadline notification advance is a setting that you can change in settings, by clicking on your username in top-right corner.'
            ],

            'publicationSearch' => [
                'title' => 'Publication search',
                'content' =>
                    'Use this form to search for publications. Select desired criteria to search by. '
                    .'You can select multiple authors, separate them with comma. All text fields are case and diacritics insensitive.'
            ],

            'publicationSearchAuthors' => [
                'title' => 'Publication search - authors',
                'content' =>
                    'Use this field to specify authors. This field will suggest authors as you type. '
                    .'You can select multiple authors, separate them with comma.'
            ],

            'publicationSearchCategories' => [
                'title' => 'Publication search - categories',
                'content' =>
                    'Use this field to specify categories. Selecting a category automatically selects all its subcategories.'
            ],

            'annotations' => [
                'title' => 'Annotations',
                'content' =>
                    'Annotation is a short text you can add to publication. '
                    .'Its visibility can be private or global.'
            ],

            'tags' => [
                'title' => 'Tags',
                'content' =>
                    'You can assign your tags to publications. '
                    .'In order to assign tag to publication, you first need to create new tag by clicking Create new tag button. '
                    .'Then you can add existing tag to this publication by clicking Add tag to publication button.'
            ],

            'workshops' => [
                'title' => 'Workshop',
                'content' =>
                    'Workshop is basically standalone conference year, that is associated with another conference\'s year. '
                    .'See workshops for this conference year below. '
                    .'Add new workshop to this conference year using + button below.'
            ],

            'deadlineAdvance' => [
                'title' => 'Deadline notification advance',
                'content' =>
                    'This is a period in days that is used to highlight upcoming conferences. '
                    .'If conference year has one of the dates less than set days from today, the conference will be highlighted in the conference table and on conference detail page. '
                    .'You can set values between 1 and 99 days.'
            ]
        ];
    }

    /**
     * @return array
     */
    public function getHelp(): array
    {
        return $this->arrayHelp;
    }

}
