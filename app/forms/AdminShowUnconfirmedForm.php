<?php

use Nette\Application\UI,
    Nette\ComponentModel\IContainer,
    Nette\Diagnostics\Debugger;

class AdminShowUnconfirmedForm extends UI\Form {

    public function __construct(IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        Debugger::fireLog('AdminShowUnconfirmedForm');
        foreach ($parent->records as $record) {
            $this->addCheckbox('confirm_' . $record->id, '');
        }

        $this->addSubmit('send', 'Confirm')->setAttribute('class', 'btn btn-success');

        // $this->getElementPrototype()->class('ajax');
    }

}

?>
