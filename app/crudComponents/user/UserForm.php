<?php

namespace App\CrudComponents\User;


use App\Forms\BaseForm;
use App\Services\Authenticators\BaseAuthenticator;
use Nette\Security\User;
use PublicationFormRules;

class UserForm extends BaseForm implements \App\Forms\IMixtureForm {


    /**
     * UserForm constructor.
     * @param User $loggedUser
     * @param $availableRoles
     * @param $availableCuGroups
     * @param $availableAuthTypes
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(User $loggedUser,
                                $availableRoles,
                                $availableCuGroups,
                                $availableAuthTypes,
		                        \Nette\ComponentModel\IContainer $parent = NULL,
                                string $name = NULL)
    {
		parent::__construct($parent, $name);

		$requiredFieldsOnLoginPass = [];
		$requiredFieldsOnShibboleth = [];

		/**
		 * @var $requiredFieldsOnLoginPass \Nette\Forms\Controls\TextBase[]
		 * @var $requiredFieldsOnShibboleth \Nette\Forms\Controls\TextBase[]
		 */

		// todo nl nickname \n login
		$this->addText('nickname', "Nickname (login)")
			->addRule($this::MAX_LENGTH, 'Nickname is way too long', 100)
            ->setRequired('Nickname is required.');

		$requiredFieldsOnLoginPass[] =
			$this->addText('name', 'Name')
                ->addRule($this::MAX_LENGTH, 'Name is way too long.', 50)
                ->setRequired('Name is required.');

		$requiredFieldsOnLoginPass[] =
			$this->addText('surname', 'Surname')
                ->addRule($this::MAX_LENGTH, 'Surname is way too long.', 100)
                ->setRequired('Surname is required.');

		$emailField = $this->addText('email', 'E-mail')
				->addRule($this::EMAIL, 'E-mail is not in correct form.')
				->addRule($this::MAX_LENGTH, 'Email is way too long.', 200)
                ->setRequired('E-mail is required.');

		$requiredFieldsOnLoginPass[] = $emailField;
		$requiredFieldsOnShibboleth[] = $emailField;

		if ($loggedUser->isInRole('admin')) {
			$this->addCheckboxList('roles', 'Rights', $availableRoles)
                ->addRule(PublicationFormRules::AT_LEAST_ONE_CHECKED, "At least one role must be checked!")
                ->setRequired(false);
		}

		// todo add description for auth_type field - see below
		/**
		 *  LDAP ~ all user credentials will be deleted
		 *  Shibboleth ~ all user credentials will be deleted
		 *  Login as password ~ new credentials will be activated and will be emailed to user
		 */
		$authTypeField = $this->addRadioList('auth_type', 'Authentication type', $availableAuthTypes)
            ->setRequired('Auth type is required');

		$this->addMultiSelect('cu_groups', 'Conference user groups', $availableCuGroups);

		// add buttons
		$this->addCloseButton('cancel', 'Cancel');
		$this->addSubmit('send', 'Done');

		// add conditional validators
		foreach($requiredFieldsOnLoginPass as $reqField) {
			$reqField->addConditionOn($authTypeField, function ($f) {
			        return $f->value == BaseAuthenticator::AUTH_LOGIN_PASS;
			    })
				->setRequired(sprintf('%s is required', $reqField->label->getText()));
		}

		foreach($requiredFieldsOnShibboleth as $reqField) {
			$reqField->addConditionOn($authTypeField, function ($f) {
			        return $f->value == BaseAuthenticator::AUTH_SHIBBOLETH;
		    	})
				->setRequired(sprintf('%s is required', $reqField->label->getText()));
		}

		$this->setModal(true);
		$this->setAjax(true);
		$this->setLabelsSize(3);
	}

	public function removeConferencePart(): void
    {
		unset($this['cu_groups']);
	}

	public function removePublicationPart(): void
    {

	}

}