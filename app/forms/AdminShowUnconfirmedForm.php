<?php

use Nette\Application\UI,
    Nette\ComponentModel\IContainer;

class AdminShowUnconfirmedForm extends UI\Form {

    public function __construct(IContainer $parent = NULL, $name = NULL) {
        parent::__construct();
        if ($parent) {
            $parent->addComponent($this, $name);
        }

        foreach ($parent->records as $record) {
            $this->addCheckbox('confirm_' . $record->id, '');
        }

        $this->addSubmit('send', 'Confirm')->setAttribute('class', 'btn btn-success');

        // $this->getElementPrototype()->class('ajax');
    }

}

?>
