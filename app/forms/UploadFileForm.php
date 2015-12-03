<?php

use Nette\Application\UI,
    Nette\ComponentModel\IContainer;

class UploadFileForm extends UI\Form {

    public function __construct(IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        /*
        $this->addUpload('file', 'File:');
        $this->addHidden('folderId');
        $this->addSubmit('send', 'Upload');
        $this->getElementPrototype()->class('ajax');
         */
        $this->getElementPrototype()->class[] = "ajax";

        /* $form->addText("test","Textové políčko")
          ->addRule(Form::FILLED, "Textové políčko test musí být vyplněno!"); */

        // Uploadů můžete do formuláře samozdřejmě přidat více, ale zatím je docela nepříjemná validace a jedna chybka v JS
        $this->addMultipleFileUpload("upload", "Attachments");
        //->addRule("MultipleFileUpload::validateFilled","Musíte odeslat alespoň jeden soubor!")
        //->addRule("MultipleFileUpload::validateFileSize","Soubory jsou dohromady moc veliké!",100*1024);
        //$form->addMultipleFileUpload("upload2","Druhý balíček souborů");

        $this->addSubmit("odeslat", "Odeslat");
    }

}
