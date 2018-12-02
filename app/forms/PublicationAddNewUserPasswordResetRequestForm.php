<?php

use App\Model\Submitter;
use Nette\Application\UI;
use Nette\ComponentModel\IContainer;

class PublicationAddNewUserPasswordResetRequestForm extends UI\Form {

    /**
     * PublicationAddNewUserPasswordResetRequestForm constructor.
     * @param Submitter $submitterModel
     * @param IContainer|NULL $parent
     * @param string|NULL $name
     */
    public function __construct(Submitter $submitterModel, IContainer $parent = NULL, string $name = NULL)
    {
        parent::__construct($parent, $name);

        $this->addText('email', 'E-mail or Login')->setRequired('E-mail or Login is required.')
            ->addRule(function ($item) use ($submitterModel) {
                if($submitterModel->findByLoginOrEmail($item->value)) return true;
                return false;
            }, "E-mail or Login does not exist.", $parent);

        $this->addHidden('id');

        $this->addSubmit('send', 'Done');

        $this->getElementPrototype()->class('ajax');
        $this->addProtection('Security token has expired, please submit the form again.');
    }

}
