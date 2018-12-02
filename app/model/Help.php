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
            "publicationAddNewForm" => "If you want to EDIT some field value, click on the EDIT button. The form for editing data will be displayed. "
            . "If you want to DELETE some field value, click the DELETE button. "
            . "To display a list of all publications of field value, click the ASSOCIATED PUBLICATIONS button. "
            . "If you want to ADD NEW field value, click the ADD NEW button. The form for adding new data will be displayed. ",

            "pub_type" => "Select type of your publication and then fill all the required fields (marked by red star).",

            "authors" => "There is a list of all authors in the ALL AUTHORS box. "
            . "If you want to select some authors, please drag&drop (or use SELECT button) them to the SELECTED AUTHORS box. "
            . "If you want to sort all the authors in the SELECTED AUTHORS box, just use drag&drop (or use MOVE UP/MOVE DOWN button). "
            . "Author with the highest priority is on the first place.",

            "categories" => "There is a list of all categories in the CATEGORIES box. "
            . "If you want to select some categories, please check them. "
            . "If you want to do some action (EDIT, DELETE, ASSOCIATED PUBLICATIONS) with them, just mark them and then press one of the action buttons. "
            . "If you want to ADD NEW parent category, just click ADD NEW button and fill in a form."
            . "If you want to ADD NEW child category, just mark parent category, click ADD NEW button and fill in a form. ",

            "upload" => "If you want to upload documents, please drag&drop (or use ADD FILES button) them to the upload area. "
            . "When you are done, just click START UPLOAD button. "
            . "If you want to delete them, just click delete button.",

            "attributes" => "You can use them to extend set of publication attributes. "
            . "If you cannot find the corresponding attribute for your publication, just ADD NEW one. "
            . "The first character must be underline, other characters may be letters and numbers.",

            "groups" => "There is a list of all user groups in the GROUPS box. "
            . "If you want this publication to be part of some groups, please check them. "
            . "If you want to do some action (EDIT, DELETE, ASSOCIATED PUBLICATIONS) with them, just mark them and then press one of the action buttons.",

            "springer" => "If you want to fetch some data from Springer, please fill in the form correctly, then click FETCH DATA button. "
            . "In the next step, select the data for import and click IMPORT DATA button.",

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
