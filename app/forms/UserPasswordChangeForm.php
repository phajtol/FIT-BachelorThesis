<?php

use App\Forms\UserPasswordForm;
use App\Services\Authenticators\LoginPassAuthenticator;
use Nette\ComponentModel\IContainer;

class UserPasswordChangeForm extends UserPasswordForm {

    /**
     * UserPasswordChangeForm constructor.
     * @param LoginPassAuthenticator $loginPassAuthenticator
     * @param $user
     * @param IContainer|NULL $parent
     * @param string|NULL $name
     */
    public function __construct(LoginPassAuthenticator $loginPassAuthenticator, $user, IContainer $parent = NULL, string $name = NULL)
    {
        parent::__construct($parent, $name);

        $this->addPassword('pass_old', 'Old Password')
            ->setRequired('Old Password is required.')
            ->addRule(function ($field) use ($loginPassAuthenticator, $user) {
                try {
                    $loginPassAuthenticator->authenticate([$user->nickname, $field->value]);
                } catch (Nette\Security\AuthenticationException $e) {
                    return false;
                }
                return true;
            }, 'Wrong old password set');

        $this->addSubmit('send', 'Done');

        $this->getElementPrototype()->class('ajax');
    }

}
