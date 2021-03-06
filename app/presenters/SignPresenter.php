<?php

namespace App\Presenters;

use App\Forms\UserPasswordResetForm;
use App\Services\Authenticators\BaseAuthenticator;
use Nette;
use App\Model;


/**
 * Sign in/out presenters.
 */
use Nette\Diagnostics\Debugger;

class SignPresenter extends BasePresenter {

    /** @persistent */
    public $backlink = '';

    /** @var \App\Services\Authenticators\LoginPassAuthenticator */
    protected $loginPassAuthenticator;

    /** @var \App\Services\Authenticators\BaseAuthenticator */
    protected $baseAuthenticator;


    /** @var \App\Services\PasswordResetter */
    protected $passwordResetter;

    /** @var \App\Services\Authenticators\ShibbolethAuthenticator */
    protected $shibbolethAuthenticator;

    /** @var  */
    protected $passwordResetFormEnabled;

    /** @var  */
    protected $retrieveUid;


    /**
     * @param \App\Services\Authenticators\LoginPassAuthenticator $loginPassAuthenticator
     */
    public function injectLoginPassAuthenticator(\App\Services\Authenticators\LoginPassAuthenticator $loginPassAuthenticator): void
    {
        if(!$this->loginPassAuthenticator)
            $this->loginPassAuthenticator = $loginPassAuthenticator;
    }

    /**
     * @param \App\Services\Authenticators\BaseAuthenticator $baseAuthenticator
     */
    public function injectBaseAuthenticator(\App\Services\Authenticators\BaseAuthenticator $baseAuthenticator): void
    {
        $this->baseAuthenticator = $baseAuthenticator;
    }

    /**
     * @param \App\Services\PasswordResetter $passwordResetter
     */
    public function injectPasswordResetter(\App\Services\PasswordResetter $passwordResetter): void
    {
        $this->passwordResetter = $passwordResetter;

        $this->passwordResetter->setCallbackGetResetLink(function ($hash) {
            return $this->link('//reset', $hash);
        });
    }

    /**
     * @param Model\Submitter $submitterModel
     */
    public function injectSubmitterModel(Model\Submitter $submitterModel): void
    {
        if(!$this->submitterModel)
            $this->submitterModel = $submitterModel;
    }

    /**
     * @param \App\Services\Authenticators\ShibbolethAuthenticator $shibbolethAuthenticator
     */
    public function injectShibbolethAuthenticator(\App\Services\Authenticators\ShibbolethAuthenticator $shibbolethAuthenticator): void
    {
        $this->shibbolethAuthenticator = $shibbolethAuthenticator;
    }



    // --------

    /**
     *
     */
    public function actionIn(): void
    {
        $this->template->resetLinkSent = false;

        if ($this->user->isLoggedIn()) {
            $this->redirectUser();
        }
    }

    /**
     * @throws Nette\Application\AbortException
     */
    protected function redirectUser(): void
    {
        if ($this->isPU()) {
            $this->redirect("Homepage:default");
        }
        if ($this->isCU()) {
            $this->redirect("Conference:showall");
        }
    }

    /**
     * Sign-in form factory.
     * @param string $name
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm(string $name): Nette\Application\UI\Form
    {
        $form = new \SignInForm($this, $name);

        $form->onSuccess[] = function (Nette\Forms\Form $form) {
            $values = $form->getValues();

            if ($values->remember) {
                $this->getUser()->setExpiration('14 days', FALSE);
            } else {
                $this->getUser()->setExpiration('20 minutes', TRUE);
            }

            try {
                $this->getUser()->login($values->username, $values->password);
                $this->flashMessage('You have been signed in successfully.', 'alert-success');
                $this->presenter->restoreRequest($this->backlink);
                $this->presenter->redirect("Sign:in");
            } catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };

        return $form;
    }

    protected function createComponentPublicationAddNewUserPasswordResetRequestForm(string $name): \PublicationAddNewUserPasswordResetRequestForm
    {
        $form = new \PublicationAddNewUserPasswordResetRequestForm($this->submitterModel, $this, $name);

        $form->onError[] = function (\PublicationAddNewUserPasswordResetRequestForm $form) {
            $this->redrawControl('publicationAddNewUserPasswordResetRequestForm');
        };

        $form->onSuccess[] = function (\PublicationAddNewUserPasswordResetRequestForm $form) {
            $formValues = $form->getValues();
            $this->drawAllowed = true;

            // reset form if ajax call in use
            if($this->isAjax()) {
                $form->setValues([], TRUE);
                $this->redrawControl('publicationAddNewUserPasswordResetRequestForm');
            }

            $record = $this->submitterModel->findByLoginOrEmail($formValues['email']);

            if ($record) {
                if ($this->baseAuthenticator->getUserAuthenticationMethod($record->id) != BaseAuthenticator::AUTH_LOGIN_PASS) {
                    $this->finalFlashMessage("Given user has not associated internal credentials and uses different authentication method. Please contact site administrator.", 'alert-danger');
                } else {
                    $this->passwordResetter->createPasswordRetrieve($record);
                    $this->finalFlashMessage('Reset link has been sent to your email: ' . $record->email, 'alert-success');
                    $this->template->resetLinkSent = true;
                }
            } else {
                $this->finalFlashMessage("Login or email you've entered hasn't been found", 'alert-danger');
            }
        };

        return $form;
    }

    /**
     * @param string $hash
     */
    public function actionReset(string $hash): void
    {
        $this->passwordResetFormEnabled = false;

        if (!$hash) {
            $this->errorFlashMessage('The password retrieve hash must be set!');
        } else {
            if ($this->passwordResetter->checkRetrieveHashIsValid($hash)) {
                $this->passwordResetFormEnabled = true;
                $this->retrieveUid = $hash;
            } else {
                $this->errorFlashMessage('Wrong reset link, password has not been changed!');
            }
        }
    }

    /**
     * @param string $formName
     * @return UserPasswordResetForm
     */
    public function createComponentPasswordResetForm(string $formName): UserPasswordResetForm
    {
        $form = new UserPasswordResetForm($this->passwordResetter);

        if (!$this->passwordResetFormEnabled) {
            return $form; //asi by tu malo by?? null?
        }

        $form['retrieve_uid']->setValue($this->retrieveUid);
        $passwordResetter = $this->passwordResetter;

        $form->onSuccess[] = function (UserPasswordResetForm $form) use ($passwordResetter) {
            $login = $passwordResetter->performPasswordChange($form['retrieve_uid']->getValue(), $form['pass']->getValue());
            $this->successFlashMessage('Your password has been changed successfully. Your login is ' . $login);
            $this->passwordResetFormEnabled = false;
            $this->redirect('in');
        };

        $form->onError[] = function () use ($formName) {
            $this->errorFlashMessage('Your password has not been changed!');
            $this->redrawControl($formName);
        };

        return $form;
    }

    /**
     *
     */
    public function renderReset(): void
    {
        $this->template->passwordResetFormEnabled = $this->passwordResetFormEnabled;
    }

    /**
     * @throws Nette\Application\AbortException
     */
    public function actionOut(): void
    {
        $this->getUser()->logout();
        $this->flashMessage('You have been signed out successfully.', 'alert-success');
        $this->presenter->redirect('in');
    }



    // --- shibboleth ---

    /**
     * @throws Nette\Application\AbortException
     */
    public function actionShibboleth(): void
    {
        // parse shibboleth vars
        $shibbVars = [];
        $varsPrefix = 'REDIRECT_';

        foreach ($_SERVER as $k => $v) {
            if (substr($k, 0, strlen($varsPrefix)) == $varsPrefix) {
                $shibbVars[substr($k, strlen($varsPrefix))] = $v;
            }
        }

        if (empty($shibbVars)) {
            $shibbVars = $_SERVER;
        }

        $this->user->setAuthenticator($this->shibbolethAuthenticator);

        try {
            $this->user->login($shibbVars);
        } catch(Nette\Security\AuthenticationException $e) {
            $this->errorFlashMessage($e->getMessage());
        }

        $this->redirect('Sign:in');
    }

}
